<?php

namespace Bananacode\Kipping\Ui\Component\Listing\Column;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class KippingDelivery
 * @package Bananacode\Kipping\Ui\Component\Listing\Column
 */
class KippingDelivery extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Hacienda constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        ModuleDataSetupInterface $moduleDataSetup,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        $this->moduleDataSetup = $moduleDataSetup;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $this->addDeliveryInSalesOrderGrid();
            $this->addisUsedCoupon();
            $this->addlatlng();            
            $this->addQtycount();
            foreach ($dataSource['data']['items'] as &$item) {
                $order = $this->_orderRepository->get($item["entity_id"]);
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $order->getKippingDelivery());
                setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
                if($date) {
                    if ($order->getShippingMethod() == 'kipping_scheduled' || $order->getShippingMethod() == 'kipping_scheduled_today' || $order->getShippingMethod() == 'kipping_scheduled_festivity') {
                        $date->sub(new \DateInterval('PT6H')); // GMT-06:00;
                    } else {
                        $date->setTimezone(new \DateTimeZone("America/El_Salvador"));
                    }
                    $item['kipping_delivery'] = ucfirst(strftime("%h %d, %Y %I:%M:%S %p", strtotime($date->format('Y-m-d H:i:s'))));                            
                }                   
                    // $item['count_orders'] = $this->addOrderCount($item['customer_id']);
                    $item['count_orders'] = $this->countOrders($item['customer_id']);
            }
        }

        return $dataSource;
    }

    /**
     * addDeliveryInSalesOrderGrid
     */
    private function addDeliveryInSalesOrderGrid()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'kipping_delivery') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            'kipping_delivery'
                        ),
                    $destinationTable
                )
            );
        }
    }

    /**
     * addisUsedCoupon
     */
    private function addisUsedCoupon()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'coupon_code') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            'coupon_code'
                        ),
                    $destinationTable
                )
            );
        }
    }


    /**
     * addlat
     */
    private function addlatlng()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'latlng') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            ['latlng' => "CONCAT(address_latitude, ' ', address_longitude)"]
                        ),
                    $destinationTable
                )
            );
        }
    }

    /**
     * addlat
     */
    private function addOrderCount($id)
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'count_orders') === true) {
            $cn = $this->countOrders($id);
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            ['count_orders' => $cn ]
                        ),
                    $destinationTable
                )
            );
        }
    }

    private function countOrders($id)
    {
        $select = $this->moduleDataSetup->getConnection()->select();
        $select->from(
            ['orders' => $this->moduleDataSetup->getTable('sales_order')],
            ['orders.entity_id']
        )
            ->where('orders.customer_id = ?', $id);
        $orders = $this->moduleDataSetup->getConnection()->fetchAll($select);
        // $totalOrderCount = (int) $connection->fetchOne($select);
        return count($orders);
    }
   
    /**
     * addQtycount
     */
    private function addQtycount()
    {
        $connection       = $this->moduleDataSetup->getConnection();
        $sourceTable      = $this->moduleDataSetup->getTable('sales_order');
        $destinationTable = $this->moduleDataSetup->getTable('sales_order_grid');
        if ($connection->tableColumnExists($this->moduleDataSetup->getTable('sales_order_grid'), 'total_qty_ordered') === true) {
            $connection->query(
                $connection->updateFromSelect(
                    $connection->select()
                        ->join(
                            $sourceTable,
                            sprintf('%s.entity_id = %s.entity_id', $destinationTable, $sourceTable),
                            'total_qty_ordered'
                        ),
                    $destinationTable
                )
            );
        }
    }

}
