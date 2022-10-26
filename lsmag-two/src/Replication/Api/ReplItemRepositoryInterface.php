<?php

namespace Ls\Replication\Api;

use Ls\Replication\Api\Data\ReplItemInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */
interface ReplItemRepositoryInterface
{

    public function getList(SearchCriteriaInterface $criteria);

    public function save(ReplItemInterface $page);

    public function delete(ReplItemInterface $page);

    public function getById($id);

    public function deleteById($id);


}

