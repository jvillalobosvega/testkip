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

/**
 * Class CaptureRequest
 * @package Bananacode\BacFac\Gateway\Request
 */
class CaptureRequest implements BuilderInterface
{
    use Formatter;

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
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        ConfigInterface $config,
        SubjectReader $subjectReader,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
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
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $order_billing_address = $this->getAddressData($order->getBillingAddress());
        $order_shipping_address = $this->getAddressData($order->getShippingAddress());

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }

        $sandbox = $this->config->getValue(
            'sandbox',
            $order->getStoreId()
        );

        $ekey = str_split($this->config->getValue('acquirer_id') . $this->config->getValue('merchant_id'),3);

        return [
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
