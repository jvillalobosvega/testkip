<?php

namespace Bananacode\BacFac\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;

/**
 * Class BacFacHandler
 * @package Bananacode\BacFac\Gateway\Response
 */
class BacFacHandler implements HandlerInterface
{
    const CODE = 'bacfac';

    const PROD_URL =    'https://marlin.firstatlanticcommerce.com/PGService/';

    const SANDBOX_URL = 'https://ecm.firstatlanticcommerce.com/PGService/';

    /**
     * @var PaymentTokenInterface
     */
    private $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var \Magento\Payment\Gateway\Config\Config
     */
    protected $_config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var PaymentToken
     */
    protected $_paymentToken;

    /**
     * @var PaymentTokenRepository
     */
    protected $_paymentTokenRepository;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * BacFacHandler constructor.
     * @param PaymentTokenInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param \Magento\Payment\Gateway\Config\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param PaymentToken $paymentToken
     * @param PaymentTokenRepository $paymentTokenRepository
     */
    public function __construct(
        \Magento\Vault\Api\Data\PaymentTokenInterface $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        \Magento\Payment\Gateway\Config\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        PaymentToken $paymentToken,
        PaymentTokenRepository $paymentTokenRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;

        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->_paymentToken = $paymentToken;
        $this->_paymentTokenRepository = $paymentTokenRepository;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->_logger = new \Monolog\Logger('Bacfac');
        $this->_logger->pushHandler($logHandler);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->_logger = new \Monolog\Logger('Bacfac');
        $this->_logger->pushHandler($logHandler);
       
        $this->_logger->addInfo(print_r("=======>".json_encode($handlingSubject) , true));
        $this->_logger->addInfo(print_r("-=-=-=-=-=>".json_encode($response) , true));
        

        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            $this->_logger->addInfo(print_r("NO PAYMENT-->" , true));
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        //Check for vault payment
        $paymentToken = $this->getVaultPaymentToken($response, $order, $payment->getAdditionalInformation('bin'));
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }

        //Get transaction id
        $transactionId = isset($response['TransactionIdentifier']) ? $response['TransactionIdentifier'] : null;
        if(!$transactionId) {
            $transactionId = isset($response['TransactionIdentifier']) ? $response['TransactionIdentifier'] : null;
        }

        if($transactionId) {
            $payment->setTransactionId($transactionId);
        } else {
            $payment->setTransactionId('bacfac-error');
        }

        //CC Last4
        if(isset($response['PaddedCardNo'])) {
            $last4 = substr($response['PaddedCardNo'], strlen($response['PaddedCardNo']) - 4);
            $payment->setCcLast4($last4);
        }

        //CC Type
        if(isset($response['cc_type'])) {
            $payment->setCcType($response['cc_type']);
        }

        $payment->setAdditionalInformation('response', json_encode($response));
    }

    /**
     * @param $transaction
     * @param \Magento\Payment\Gateway\Data\OrderAdapterInterface $order
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken($transaction, $order, $bin)
    {
        
        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->_logger = new \Monolog\Logger('Bacfac');
        $this->_logger->pushHandler($logHandler);
        // $this->_logger->addInfo(print_r("P" , true));
        $this->_logger->addInfo(print_r("getVaultPaymentToken".json_encode($transaction) , true));

        if(isset($transaction['vault']) && isset($transaction['PanToken'])) {
            if ($transaction['vault'] && $transaction['PanToken'] && $order->getCustomerId()) {
                $ccn = $this->_checkoutSession->getVarValue();
               
                try {
                    $this->_paymentToken
                        ->setCustomerId($order->getCustomerId())
                        ->setPaymentMethodCode('bacfac')
                        ->setGatewayToken($transaction['PanToken'])
                        ->setType(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD)
                        ->setPublicHash($this->_encryptor->encrypt($transaction['PanToken']))
                        ->setTokenDetails(json_encode([
                            "maskedCC" => $ccn,#PaddedCardNo
                            "expirationDate" => $transaction['cc_expirationDate'],
                            "type" => $transaction['cc_type'],
                            "bin" => $bin,
                        ]))
                        ->setIsActive(true)
                        ->setIsVisible(true)
                        ->setExpiresAt(date('Y-m-d H:i:s', strtotime('+1 year')));

                    $this->_paymentTokenRepository->save($this->_paymentToken);

                    return $this->_paymentToken;
                } catch (\Exception $e) {
                    $this->_logger->addError(print_r($e->getMessage(), true));
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Get payment extension attributes
     *
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        // $this->logger->addInfo(print_r("BacFacHandler getExtensionAttributes!!!!!" , true));

        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
