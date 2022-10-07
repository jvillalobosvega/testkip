<?php

namespace Bananacode\TaxDocument\Model\ResourceModel\TaxDocument;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'document_id';
	protected $_eventPrefix = 'bananacode_taxdocument_collection';
	protected $_eventObject = 'taxdocument_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Bananacode\TaxDocument\Model\TaxDocument', 'Bananacode\TaxDocument\Model\ResourceModel\TaxDocument');
	}

}