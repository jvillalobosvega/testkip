<?php

namespace Bananacode\Kipping\Plugin\Checkout;

/**
 * Class SaveAddressInformation
 */
class SaveAddressInformation
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Checkout\Api\Data\ShippingInformationInterface
     */
    private $_shippingInformation;

    /**
     * SaveAddressInformation constructor.
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $_shippingInformation
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Api\Data\ShippingInformationInterface $_shippingInformation,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_shippingInformation = $_shippingInformation;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\StateException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        /*$kippingData = json_decode($this->_checkoutSession->getKippingData(), true);
        if (isset($kippingData['method'])) {
            if (!isset($kippingData['package'])) {
                throw new \Magento\Framework\Exception\StateException(
                    __('Favor seleccionar tipo de empaque.')
                );
            }

            if ($kippingData['method'] === 'scheduled') {
                if (!isset($kippingData['scheduled_day']) && !isset($kippingData['scheduled_hour'])) {
                    throw new \Magento\Framework\Exception\StateException(
                        __('Favor seleccionar fecha y hora de entrega para tu pedido programado.')
                    );
                }
            }
        } else {
            throw new \Magento\Framework\Exception\StateException(
                __('Favor seleccionar tipo de envío.')
            );
        }*/

        /**
         * Shipping Address
         */
        $shippingAddress = $addressInformation->getShippingAddress();
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        if ($shippingAddressExtensionAttributes) {
            $latitude = $shippingAddressExtensionAttributes->getAddressLatitude();
            $longitude = $shippingAddressExtensionAttributes->getAddressLongitude();
            $shippingAddress->setAddressLatitude($latitude);
            $shippingAddress->setAddressLongitude($longitude);
        }
    }
}
