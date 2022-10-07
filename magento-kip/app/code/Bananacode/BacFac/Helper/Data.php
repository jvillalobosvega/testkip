<?php

namespace Bananacode\BacFac\Helper;

use Bananacode\BacFac\Model\Ui\ConfigProvider as BacFacProvider;
use Ls\Core\Model\LSR;
use Ls\Omni\Helper\BasketHelper;
use Ls\Omni\Helper\LoyaltyHelper;
use Magento\Payment\Gateway\Config\Config;
use Magento\Payment\Model\Method\Logger;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Config
     */
    public $_config;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var LoyaltyHelper
     */
    private $loyaltyHelper;

    /**
     * @var BasketHelper
     */
    private $basketHelper;

    /**
     * @var LSR
     */
    public $lsr;

    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    private $_cart;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $_quoteRepository;

    /**
     * Data constructor.
     * @param Config $config
     * @param Logger $logger
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Config $config,
        Logger $logger,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        LoyaltyHelper $loyaltyHelper,
        BasketHelper $basketHelper,
        LSR $lsr,
        \Magento\Quote\Model\Cart\CartTotalRepository $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        $this->_config = $config;
        $this->_encryptor = $encryptor;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_formKey = $formKey;

        $this->loyaltyHelper = $loyaltyHelper;
        $this->basketHelper = $basketHelper;
        $this->lsr = $lsr;
        $this->_cart = $cart;
        $this->_quoteRepository = $quoteRepository;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->logger = new \Monolog\Logger('Bacfac');
        $this->logger->pushHandler($logHandler);

        parent::__construct($context);
    }

    /**
     * @param $card_details
     * @param $order_increment_id
     * @param null $amount
     * @param bool $is_3ds
     * @param $customerId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generate_request($card_details, $order_increment_id, $amount = null, $is_3ds = true, $customerId = null)
    {
        $this->_config->setMethodCode(BacFacProvider::CODE);
        $sandbox = $this->_config->getValue('sandbox');
        $api_url = $sandbox ? BacFacProvider::SANDBOX_URL : BacFacProvider::PROD_URL;
        $order_increment_id = $order_increment_id . '-' . time();
        $amount = $amount ?? $this->getOrderAmount($customerId);
        $data_request = [
            'Request' => [
                "TransactionDetails" => $this->getTransactionDetails($order_increment_id, $amount, $card_details),
                "CardDetails" => $this->getCardDetails($card_details)
            ]
        ];

        if ($is_3ds) {
            $callBack = $this->_storeManager->getStore()->getUrl('bacfac/checkout/form');
            if (isset($card_details['is_vault'])) {
                if($card_details['is_vault']) {
                    $callBack = $this->_storeManager->getStore()->getUrl('bacfac/checkout/vault');
                }
            }

            $callBack .= '?form_key=' . $this->_formKey->getFormKey();
            if (isset($card_details['vault_id'])) {
                $callBack .= '&vault_id=' . $card_details['vault_id'];
            }

            $data_request["Request"]["MerchantResponseURL"] = $callBack;
        }

        return $this->sendData($data_request, $api_url, $is_3ds);
    }

    /**
     * @param $data_request
     * @param $api_url
     * @param bool $is_3ds
     * @return mixed
     */
    public function sendData($data_request, $api_url, $is_3ds = true)
    {
        $options = ['location' => $api_url . 'Services.svc', 'soap_version' => SOAP_1_1, 'exceptions' => 0, 'trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE];

        $wsdlurl = $api_url . "Services.svc?wsdl";

        $client = new \SoapClient($wsdlurl, $options);

        if ($is_3ds) {
            $result = $client->Authorize3DS($data_request);
        } else {
            $result = $client->Authorize($data_request);
        }

        $this->logger->addInfo(print_r(json_encode($result), true));

        return $result;
    }

    /**
     * @param $password
     * @param $facId
     * @param $acquirerId
     * @param $orderNumber
     * @param $amount
     * @param $currency
     * @return string
     */
    public function sign($password, $facId, $acquirerId, $orderNumber, $amount, $currency)
    {
        $stringtohash = $password . $facId . $acquirerId . $orderNumber . $amount . $currency;
        $hash = sha1($stringtohash, true);
        $signature = base64_encode($hash);
        return $signature;
    }

    /**
     * @param $incrementId
     * @param $grand_total
     * @param $card_details
     * @return array
     */
    public function getTransactionDetails($incrementId, $grand_total, $card_details)
    {
        $acquirer_id = $this->_config->getValue('acquirer_id');
        $merchant_id = $this->_config->getValue('merchant_id');
        $currency = "840";

        $password = $this->_encryptor->decrypt($this->_config->getValue('password'));
        $amount = $this->amountFormatted($grand_total);
        //$transactionCode = 0 + 8 + 128; //Authorize & Capture;
        $transactionCode = 0 + 128; //Authorize;
        if (isset($card_details['is_vault'])) {
            if($card_details['is_vault']) {
                $transactionCode -= 128;
            }
        }

        return [
            "AcquirerId" => $acquirer_id,
            "MerchantId" => $merchant_id,
            "OrderNumber" => $incrementId,
            "TransactionCode" => $transactionCode,
            "Amount" => $amount, //(por ej. $12.00 = “000000001200”)
            "Currency" => $currency,
            "CurrencyExponent" => 2,
            "SignatureMethod" => "SHA1",
            "Signature" => $this->sign($password, $merchant_id, $acquirer_id, $incrementId, $amount, $currency),
        ];
    }

    /**
     * @param $card_details
     * @return array
     */
    public function getCardDetails($card_details)
    {
        if (isset($card_details['is_vault'])) {
            if($card_details['is_vault']) {
                $card_details["credit_card_number"] = $this->_encryptor->decrypt($card_details["credit_card_number"]);
                return [
                    "CardNumber" => $card_details["credit_card_number"],
                    "CardExpiryDate" => '0000',
                    "CardCVV2" => $card_details["credit_card_security_code_number"]
                ];
            }
        }
        $exp_month = (strlen($card_details["exp_month"]) > 1) ? $card_details["exp_month"] : '0' . $card_details["exp_month"];
        $exp_year = substr($card_details["exp_year"], -2);
        return [
            "CardNumber" => $card_details["credit_card_number"],
            "CardExpiryDate" => $exp_month . $exp_year,
            "CardCVV2" => $card_details["credit_card_security_code_number"]
        ];
    }

    /**
     * @param $address
     * @return array
     */
    public function getAddressDetails($address)
    {
        return [
            "BillToAddress" => $address["street"],
            "BillToZipPostCode" => $address["postcode"],
            "BillToFirstName" => $address["firstname"],
            "BillToLastName" => $address["lastname"]
        ];
    }

    /**
     * @param $amount
     * @return string
     */
    public function amountFormatted($amount)
    {
        $factor = pow(10, 2);
        $aux1 = round($amount * floatval($factor), 0);
        $aux2 = strval($aux1);
        $aux3 = str_pad($aux2, 12, "0", STR_PAD_LEFT);
        return $aux3;
    }

    /**
     * @return float|int|null
     */
    private function getOrderAmount($customerId)
    {
        if($customerId) {
            try {
                /**
                 * @var $cart \Magento\Quote\Api\Data\CartInterface
                 */
                $cart = $this->_quoteRepository->getActiveForCustomer($customerId);
                $totals = $this->_cart->get($cart->getId());
                return $totals->getBaseGrandTotal();
            } catch (\Exception $e) {
                return -3;
            }
        } else {
            return -4;
        }
    }
}
