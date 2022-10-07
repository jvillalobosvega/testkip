<?php

namespace Bananacode\Kipping\Block\Adminhtml\Order\View;

/**
 * Class Info
 * @package Bananacode\Kipping\Block\Adminhtml\Order\View
 */
class Info extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $_context;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_orderRepository = $orderRepository;
        $this->_context = $context;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @param $path
     */
    public function getConfig($path) {
        return $this->_scopeConfig->getValue('carriers/kipping' . $path);
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @return string
     */
    public function getFormattedAddress($order)
    {
        $address       = '';
        $shipToAddress = $order->getShippingAddress();
        if (!empty($shipToAddress)) {
            $address .= $order->getCustomerFirstname() ? $order->getCustomerFirstname() . ' ' : '';
            $address .= $order->getCustomerLastname() ? $order->getCustomerLastname() . '<br/>' : '';
            $address .= isset($shipToAddress->getStreet()[0]) ? $shipToAddress->getStreet()[0] . '<br/>' : '';
            $address .= isset($shipToAddress->getStreet()[1]) ? $shipToAddress->getStreet()[1] . '<br/>' : '';
            $address .= isset($shipToAddress->getStreet()[2]) ? $shipToAddress->getStreet()[2] . '<br/>' : '';
            $address .= $shipToAddress->getCity() ? $shipToAddress->getCity() . ', ' : '';
            $address .= $shipToAddress->getRegion() ? $shipToAddress->getRegion() . ', ' : '';
            $address .= $shipToAddress->getPostCode() ? $shipToAddress->getPostCode() . '<br/>' : '';
            $address .= $shipToAddress->getTelephone() ? $shipToAddress->getTelephone() . '<br/>' : '';

            /** TODO update with Address Phone Number */
            /** Removing this field to resolve the Omni 4.13 compatibility
            $address .= $order->getShipToPhoneNumber() ?
            "<a href='tel:" . $order->getShipToPhoneNumber() . "'>" . $order->getShipToPhoneNumber() . '</a>' : '';
             */
        }
        return $address;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @return string
     */
    public function getRawAddress($order)
    {
        $address       = '';
        $shipToAddress = $order->getShippingAddress();
        if (!empty($shipToAddress)) {
            $address .= $order->getCustomerFirstname() ? $order->getCustomerFirstname() . ' ' : '';
            $address .= $order->getCustomerLastname() ? $order->getCustomerLastname() . ', ' : '';
            $address .= isset($shipToAddress->getStreet()[0]) ? $shipToAddress->getStreet()[0] . ', ' : '';
            $address .= isset($shipToAddress->getStreet()[1]) ? $shipToAddress->getStreet()[1] . ', ' : '';
            $address .= isset($shipToAddress->getStreet()[2]) ? $shipToAddress->getStreet()[2] . ', ' : '';
            $address .= $shipToAddress->getCity() ? $shipToAddress->getCity() . ', ' : '';
            $address .= $shipToAddress->getRegion() ? $shipToAddress->getRegion() . ', ' : '';
            $address .= $shipToAddress->getPostCode() ? $shipToAddress->getPostCode() . ', ' : '';
            $address .= $shipToAddress->getTelephone() ? $shipToAddress->getTelephone() : '';

            /** TODO update with Address Phone Number */
            /** Removing this field to resolve the Omni 4.13 compatibility
            $address .= $order->getShipToPhoneNumber() ?
            "<a href='tel:" . $order->getShipToPhoneNumber() . "'>" . $order->getShipToPhoneNumber() . '</a>' : '';
             */
        }
        return $address;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function disasterRecovery($order) {
        $kipData = [];
        foreach ($order->getStatusHistoryCollection() as $status) {
            if ($status->getComment()) {
                $kipProp = explode(':', $status->getComment());
                if (count($kipProp) > 1) {
                    $value = '';
                    for ($k = 1; $k < count($kipProp); $k++) {
                        $value .= $k > 1 ? ':' : '';
                        $value .= trim($kipProp[$k]);
                    }
                    $kipData[$kipProp[0]] = trim($value);
                }
            }
        }

        $save = false;

        if(!$order->getDocumentId() || empty($order->getDocumentId())) {
            if(isset($kipData['LSDocumentID'])) {
                $order->setDocumentId($kipData['LSDocumentID']);
                $save = true;
            }
        }

        if(!$order->getKippingDelivery() || empty($order->getKippingDelivery())) {
            if(isset($kipData['Fecha de entrega']) && isset($kipData['HoraEntrega'])) {
                $order->setKippingDelivery($kipData['Fecha de entrega'] . ' ' . $kipData['HoraEntrega']);
                $save = true;
            }
        }

        if($save) {
            $this->_orderRepository->save($order);
        }

        return $save;
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function getInvoiceDocument($order) {
        $kipData = [];
        foreach ($order->getStatusHistoryCollection() as $status) {
            if ($status->getComment()) {
                $kipProp = explode(':', $status->getComment());
                if (count($kipProp) > 1) {
                    $value = '';
                    for ($k = 1; $k < count($kipProp); $k++) {
                        $value .= $k > 1 ? ':' : '';
                        $value .= trim($kipProp[$k]);
                    }
                    $kipData[$kipProp[0]] = trim($value);
                }
            }
        }

        if(isset($kipData['invoice_document'])) {
            $id = intval($kipData['invoice_document']);

            if($id) {
                $link = $this->_context->getUrlBuilder()->getUrl(
                    'bananacode_taxdocument/review/approve',
                    ['document_id' => $id]
                );
                return '<a href="' . $link . '" target="_blank">Ver Documento</a>';
            }

            return 'Factura';
        } else {
            return 'Factura';
        }
    }
}
