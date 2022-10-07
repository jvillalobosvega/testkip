<?php

namespace Bananacode\TaxDocument\Model\ResourceModel\TaxCategory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'entity_id';
	protected $_eventPrefix = 'bananacode_taxcategory_collection';
	protected $_eventObject = 'taxcategory_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Bananacode\TaxDocument\Model\TaxCategory', 'Bananacode\TaxDocument\Model\ResourceModel\TaxCategory');
	}

}