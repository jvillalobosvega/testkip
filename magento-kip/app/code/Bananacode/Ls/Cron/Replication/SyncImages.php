<?php

namespace Bananacode\Ls\Cron\Replication;

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

/**
 * Creates images
 * for items and variants
 */
class SyncImages extends \Ls\Replication\Cron\SyncImages
{
    /**
     * This function is overriding in hospitality module
     * @param false $totalCount
     * @return Collection
     */
    public function getRecordsForImagesToProcess($totalCount = false)
    {
        if (!$totalCount) {
            $batchSize = $this->replicationHelper->getProductImagesBatchSize();
        } else {
            $batchSize = -1;
        }
        /** Get Images for only those items which are already processed */
        $filters = [
            ['field' => 'main_table.TableName', 'value' => 'Item%', 'condition_type' => 'like'],
            ['field' => 'main_table.TableName', 'value' => 'Item Category', 'condition_type' => 'neq'],
            ['field' => 'main_table.scope_id', 'value' => $this->store->getId(), 'condition_type' => 'eq']
        ];
        $criteria = $this->replicationHelper->buildCriteriaForArrayWithAlias(
            $filters,
            $batchSize,
            false
        );
        /** @var  $collection */
        $collection = $this->replImageLinkCollectionFactory->create();

        /** we only need unique product Id's which has any images to modify */
        $this->replicationHelper->setCollectionPropertiesPlusJoinsForImages(
            $collection,
            $criteria,
            'Item'
        );

        $collection->getSelect()->group('main_table.repl_image_link_id');

        $collection->getSelect()->order('main_table.processed ASC');

        return $collection;
    }
}
