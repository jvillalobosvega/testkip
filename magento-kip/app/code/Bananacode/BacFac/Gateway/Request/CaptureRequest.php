<?php
/**
 * Copyright © 2019 Bananacode SA, All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bananacode\BacFac\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class CaptureRequest
 * @package Bananacode\BacFac\Gateway\Request
 */
class CaptureRequest implements BuilderInterface
{
    use Formatter;


    /**
     * @var Logger
     */
    private $logger;


    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * CaptureRequest constructor.
     * @param Logger $logger
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        Logger $logger,
        ConfigInterface $config,
        SubjectReader $subjectReader,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->_encryptor = $encryptor;
    }

    /**
     * Builds required request data
     *
     * @param array $buildSubject
     * @return array
     */
    
    public function build(array $buildSubject)
    {

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->logger = new \Monolog\Logger('Bacfac');
        $this->logger->pushHandler($logHandler);
        $this->logger->addInfo(print_r("BUILD!!!>>", true));
        $this->logger->addInfo(print_r(json_encode($buildSubject), true));

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            $this->logger->addInfo(print_r("CAPTUREREQUEST NO PAYMENT DATA", true));
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $order_billing_address = $this->getAddressData($order->getBillingAddress());
        $order_shipping_address = $this->getAddressData($order->getShippingAddress());

        if (!$payment instanceof OrderPaymentInterface) {
            $this->logger->addInfo(print_r("CAPTUREREQUEST NO PAYMENT DATA Provided", true));
            throw new \LogicException('Order payment should be provided.');
        }

        $sandbox = $this->config->getValue(
            'sandbox',
            $order->getStoreId()
        );

        $ekey = str_split($this->config->getValue('acquirer_id') . $this->config->getValue('merchant_id'),3);
        $this->logger->addInfo(print_r("CAPTUREREQUEST prev acq::", true));
        $this->logger->addInfo(print_r($ekey, true));
        $rqst = 
         [
            'order_billing_address' => $order_billing_address,
            'order_shipping_address' => $order_shipping_address,
            'amount_no_format' => $this->subjectReader->readAmount($buildSubject),
            'currency' => $order->getCurrencyCode(),
            'amount' => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
            'customer_id' => $order->getCustomerId(),
            'payment_method_nonce' => $payment->getAdditionalInformation('payment_method_nonce'),
            'is_authorized' => $payment->getAdditionalInformation('is_authorized'),
            'bin' => $payment->getAdditionalInformation('bin'),
            'order_id' => $order->getOrderIncrementId(),
            'client_id' => $this->config->getValue(
                'client_id',
                $order->getStoreId()
            ),
            'merchant_id' => $this->config->getValue(
                'merchant_id',
                $order->getStoreId()
            ),
            'sandbox' => $sandbox,
            'ekey' => $ekey[0] . $ekey[1] . $ekey[2],
        ];
        $this->logger->addInfo(print_r(json_encode($rqst), true));
        return $rqst;
    }

    public function getAddressData($address)
    {
        return [
            "street" => $address->getStreetLine1(),
            "postcode" =>  $address->getPostcode(),
            "firstname" =>  $address->getFirstname(),
            "lastname" =>  $address->getLastname()
        ];
    }
}
