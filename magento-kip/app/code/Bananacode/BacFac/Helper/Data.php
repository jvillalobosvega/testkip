<?php

namespace Bananacode\BacFac\Helper;

use Bananacode\BacFac\Model\Ui\ConfigProvider as BacFacProvider;
use Ls\Core\Model\LSR;
use Ls\Omni\Helper\BasketHelper;
use Ls\Omni\Helper\LoyaltyHelper;
use Magento\Payment\Gateway\Config\Config;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Api\AddressRepositoryInterface;
use Exception;
use function Symfony\Component\String\s;


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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

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
     * @var Curl
     */
    protected $curl;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $_customerRepository;

    /**
     * Data constructor.
     * @param Config $config
     * @param Logger $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Config $config,
        Logger $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        LoyaltyHelper $loyaltyHelper,
        BasketHelper $basketHelper,
        LSR $lsr,
        \Magento\Quote\Model\Cart\CartTotalRepository $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Curl $curl,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        AddressRepositoryInterface $addressRepository
    ) {
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
        $this->curl = $curl;
        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->logger = new \Monolog\Logger('Bacfac');
        $this->logger->pushHandler($logHandler);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;

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
        $cardDetails = $this->getCardDetails($card_details);
        $userDetails = $this->getCustomerDetails($customerId,$amount);        

        $data_request = array(
            "TransactionIdentifier" => sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)),
            "TotalAmount" => $amount,
            "CurrencyCode" => 840,
            "ThreeDSecure" => true,
            "Tokenize" => false,
            "Source" => $cardDetails,
            "OrderIdentifier" => $order_increment_id,
            // "BillingAddress" => $userDetails,
            "BillingAddress" => [
                "FirstName" => "John",
                "LastName" => "Smith",
                "Line1" => "1200 Whitewall Blvd.",
                "Line2" => "Unit 15",
                "City" => "Boston",              
                "State" => "MA",
                "PostalCode" => "02116",
                "CountryCode" => "840",
                "EmailAddress" => "john.smith@gmail.com",
                "PhoneNumber" => "617-345-6790"
            ],
            "AddressMatch" => false,
            "ExtendedData" => [
                "ThreeDSecure" => [
                    "ChallengeWindowSize" => "01"
                ],
                "MerchantResponseUrl" => "string",

            ]
        );

        if ($is_3ds) {
            $callBack = $this->_storeManager->getStore()->getUrl('bacfac/checkout/form');
            if (isset($card_details['is_vault'])) {
                if ($card_details['is_vault']) {
                    $callBack = $this->_storeManager->getStore()->getUrl('bacfac/checkout/vault');
                }
            }

            $callBack .= '?form_key=' . $this->_formKey->getFormKey();
            if (isset($card_details['vault_id'])) {
                $callBack .= '&vault_id=' . $card_details['vault_id'];
            }
            $data_request["ExtendedData"]["MerchantResponseUrl"] = $callBack;
        }
        $this->logger->addInfo(print_r("FINISHING REQUEST>", true));
        $this->logger->addInfo(print_r($data_request, true));
        $this->logger->addInfo(print_r("FINISHING REQUEST<", true));
        return $this->makeHttpRequest($api_url . "Auth", $data_request);

        // return $this->sendData($data_request, $api_url, $is_3ds);
    }


    public function makeHttpRequest($url, $data = null)
    {
        if (!str_contains($url, 'http')) {
            $this->logger->addInfo(print_r("NOT CONTAINS URL:" . $url, true));
            $this->_config->setMethodCode(BacFacProvider::CODE);
            $sandbox = $this->_config->getValue('sandbox');
            $api_url = $sandbox ? BacFacProvider::SANDBOX_URL : BacFacProvider::PROD_URL;
            $n_url = $api_url . $url;
            $url = $n_url;
        }
        $response = null;
        try {
            $this->logger->addInfo(print_r("RESPONSE Start:" . $url, true));
            $webhookurl = $url;
            $payload = json_encode($data,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->logger->addInfo(print_r($payload, true));
            $ch = curl_init($webhookurl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "PowerTranz-PowerTranzId: 88803178",
                "PowerTranz-PowerTranzPassword: 9x6M24GnvCiq2fbdIXoax51P1ycqBPIR3V95Q7EjwGc4C3a1HIFKU4"
            ]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            $info = curl_getinfo($ch);
            $this->logger->addInfo(print_r("<RESPONSE", true));
            $this->logger->addInfo(print_r($info, true));
            $this->logger->addInfo(print_r($result, true));
            $this->logger->addInfo(print_r("RESPONSE/>", true));
            $response = json_decode($result, false);
            curl_close($ch);
        } catch (\Exception $e) {
            $this->logger->addInfo(print_r("RESPONSE error", true));
            $this->logger->addInfo(print_r($e, true));
        }

        return $response;
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
            if ($card_details['is_vault']) {
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
     * getAddressByID function
     *
     * @param string $address_id
     * @return JSON 
     */
    private function getAddressByID($address_id)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('quote_address');
        $sql = "SELECT * from " . $tableName . " WHERE address_id=" . $address_id;
        $result = $connection->fetchAll($sql);
        $this->logger->addInfo(print_r($sql, true));
        $this->logger->addInfo(print_r($result[0], true));
        $rs = $result[0];
        // $rs = json_encode($rs, true);

        return $rs;
    }


    /**
     *  getExpDateVault function
     *
     * @param [int] $vault_id
     * @return json
     */
    private function getExpDateVault($vault_id)
    {
        $this->logger->addInfo(print_r($vault_id, true));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('vault_payment_token');
        $sql = "SELECT details from " . $tableName . " WHERE entity_id=" . $vault_id;
        $result = $connection->fetchAll($sql);
        $this->logger->addInfo(print_r($sql, true));
        $this->logger->addInfo(print_r($result[0]['details'], true));
        $rs = $result[0]['details'];
        $rs = json_decode($rs, true);
        return $rs['expirationDate'];
    }

    /**
     * formatStoredDate function
     * @param string $dt
     * @return string
     */
    public function formatStoredDate($dt)
    {
        $allDate  = $dt;
        $comp = explode("/", $allDate);
        $mes = $comp[0];
        $anno = $comp[1];
        $anno = substr($anno, -2);

        $fin = str_replace('\\', "", $mes);
        $fin = sprintf('%02d', $fin);

        return $anno . $fin;
    }

    /**
     *  getCustomerDetails function
     *  @param $customerId
     *  @return customerDetails
     */
    public function getCustomerDetails($customerId,$amount)
    {
        $customer = $this->_customerRepository->getById($customerId);
        if ($customerId) {
            try {
                $this->logger->addInfo(print_r("getCustomerDetails", true));
                $quoteId = $this->_checkoutSession->getQuote()->getId();
                $quote = null;
                $quote = $this->_quoteRepository->get($quoteId);
                $address_data = $this->getAddressByID($quote->getBillingAddress()->getId());
                $street = $address_data['street'];
                $city = $address_data['city'];
                $region = $address_data['region'];
                // $postcode = $address_data['postcode'];
                $customer_phone = $customer->getCustomAttribute('telephone') ? $customer->getCustomAttribute('telephone')->getValue() : '8888-8888';
                $customer_phone = str_replace(" ","",$customer_phone);
                $customer_phone = str_replace("+","",$customer_phone);
                $this->logger->addInfo(print_r($customer_phone, true));
                $loggerData = "Cliente: ".$customer->getFirstname() . ' ' . $customer->getLastname().' | Contacto: '.$customer_phone.' - '.$customer->getEmail().' | Carrito: $'.strval($amount);                
                $this->logger->addInfo(print_r($loggerData, true));
                $this->_checkoutSession->setCustomerNameValue($loggerData);

                return array(
                    "FirstName" => $customer->getFirstname(),
                    "LastName" => $customer->getLastname(),
                    "Line1" => $street,
                    "Line2" => $city,
                    "City" => $region,
                    "State" => $region,
                    "CountryCode" => "840",
                    "EmailAddress" => $customer->getEmail(),
                    "PhoneNumber" => $customer_phone
                );
            } catch (\Exception $e) {
                $this->logger->addInfo(print_r("getCustomerDetails NOT FOUND:", true));
                $this->logger->addInfo(print_r($e->getMessage(), true));
                return -3;
            }
        } else {
            $this->logger->addInfo(print_r("CART NOT EXIST:", true));
            return -4;
        }
    }

    /**
     * @param $card_details
     * @return array
     */
    public function getCardDetails($card_details)
    {

        $this->logger->addInfo(print_r($card_details, true));
        if (isset($card_details['is_vault'])) {
            $this->logger->addInfo(print_r("1 GETCARD DETAILS :" . $card_details['is_vault'], true));
           
            // $this->logger->addInfo(print_r($card_details, true));
            if ($card_details['is_vault']) {

                $card_details["credit_card_number"] = $this->_encryptor->decrypt($card_details["credit_card_number"]);
                // $tst = $this->_encryptor->decrypt($card_details["expirationDate"]);
                // $this->logger->addInfo(print_r("3 GETCARD DETAILS :" . $tst, true));
                $token_type = "";

                if (strpos($card_details["credit_card_number"], "_") !== false) {
                    $token_type = "PG2";
                }

                $dt = $this->getExpDateVault(str_replace("bacfac_vault_", "", $card_details["vault_id"]));
                $this->logger->addInfo(print_r($dt, true));

                return array(
                    "CardCvv" => $card_details["credit_card_security_code_number"],
                    "CardExpiration" => $this->formatStoredDate($dt),
                    "Token" => $card_details["credit_card_number"],
                    "TokenType" => $token_type,
                    "CardholderName" => "Jhon"

                );                
            }
        }
        $exp_month = (strlen($card_details["exp_month"]) > 1) ? $card_details["exp_month"] : '0' . $card_details["exp_month"];
        $exp_year = substr($card_details["exp_year"], -2);
        // return [
        //     "CardNumber" => $card_details["credit_card_number"],
        //     "CardExpiryDate" => $exp_month . $exp_year,
        //     "CardCVV2" => $card_details["credit_card_security_code_number"]
        // ];

        $ccn = str_pad(substr($card_details["credit_card_number"], -4), strlen($card_details["credit_card_number"]), "X", STR_PAD_LEFT);
        $this->logger->addInfo(print_r("CCNNNNNNNNNNNNN>>> " . $ccn, true));
        $this->_checkoutSession->setVarValue($ccn);

        return array(

            "CardPan" => $card_details["credit_card_number"],
            "CardCvv" => $card_details["credit_card_security_code_number"],
            "CardExpiration" =>  $exp_year . $exp_month
            // ,
            // "Token" => "string",
            // "TokenType" => "string",                
            // "CardholderName" => "string"

        );
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
        $this->logger->addInfo(print_r("GETTING ORDER AMOUNNNNNNNTSs", true));
        if ($customerId) {
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
