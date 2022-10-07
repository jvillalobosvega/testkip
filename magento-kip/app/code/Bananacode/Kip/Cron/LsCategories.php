<?php

namespace Bananacode\Kip\Cron;

class LsCategories
{
    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;
    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory
     */
    protected $_productCustomOption;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productLoader;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory
     */
    protected $_optionValuesFactory;

    /**
     * LsNote constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $_productLoader
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $productCustomOption,
        \Magento\Catalog\Api\ProductRepositoryInterface $_productLoader,
        \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterfaceFactory $optionValuesFactory,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Framework\Indexer\ConfigInterface $config
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_productCustomOption = $productCustomOption;
        $this->_productLoader = $_productLoader;
        $this->_optionValuesFactory = $optionValuesFactory;

        $this->indexerFactory = $indexerFactory;
        $this->config = $config;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bananacrons.log');
        $this->_logger = new \Monolog\Logger('Bananacrons');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Set default sort by options for categories
     */
    public function execute()
    {
        $this->_logger->addInfo(print_r('------------------------------------', true));
        $this->_logger->addInfo(print_r('------------ LsCategories ----------', true));
        $this->_logger->addInfo(print_r('------------------------------------', true));

        $connection = $this->_resourceConnection->getConnection();

        $sql = "SELECT * FROM `eav_attribute` WHERE `attribute_code` = 'default_sort_by';";
        $sort_by = $connection->fetchOne($sql);

        $sql = "SELECT * FROM `eav_attribute` WHERE `attribute_code` = 'available_sort_by';";
        $available_sort_by = $connection->fetchOne($sql);

        $sql = "select * from catalog_category_entity;";
        $categories = $connection->fetchAll($sql);

        foreach ($categories as $category) {
            try {
                $sql = "INSERT INTO `catalog_category_entity_varchar` (attribute_id, store_id, entity_id, value) VALUES(" . $sort_by . ", " . 0 . ", " . $category['entity_id'] . ", 'ls_marca')";
                $connection->query($sql);
                $sql = "INSERT INTO `catalog_category_entity_text` (attribute_id, store_id, entity_id, value) VALUES(" . $available_sort_by . ", " . 0 . ", " . $category['entity_id'] . ", 'name,price,ls_marca')";
                $connection->query($sql);
            } catch (\Exception $e) {}
        }

        $this->reindexAll();

        $this->_logger->addInfo(print_r('LsCategories ready...', true));
    }

    /**
     * Regenerate all index
     *
     * @return void
     * @throws \Exception
     */
    private function reindexAll(){
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create()->load($indexerId);
            $indexer->reindexAll();
        }
    }
}
