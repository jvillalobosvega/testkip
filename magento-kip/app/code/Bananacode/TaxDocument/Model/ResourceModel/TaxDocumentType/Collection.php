<?php

namespace Bananacode\TaxDocument\Model\ResourceModel\TaxDocumentType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'entity_id';
	protected $_eventPrefix = 'bananacode_taxdocumenttype_collection';
	protected $_eventObject = 'taxdocumenttype_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Bananacode\TaxDocument\Model\TaxDocumentType', 'Bananacode\TaxDocument\Model\ResourceModel\TaxDocumentType');
	}

}