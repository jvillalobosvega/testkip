<?php

namespace Bananacode\Ls\Cron;

use Exception;
use \Ls\Core\Model\LSR;
use \Ls\Replication\Model\ReplImageLink;
use \Ls\Replication\Model\ReplImageLinkSearchResults;
use \Ls\Replication\Model\ResourceModel\ReplImageLink\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Creates images
 * for items and variants
 */
class DisabledItemUnitOfMeasuresTask extends \Ls\Replication\Cron\ReplEcommItemUnitOfMeasuresTask
{
    /**
     * @param null $storeData
     */
    public function execute($storeData = null)
    {
        die('Disabled cronjob....');
    }
}
