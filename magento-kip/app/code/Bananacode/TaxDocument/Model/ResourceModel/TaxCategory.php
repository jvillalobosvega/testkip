<?php

namespace Bananacode\TaxDocument\Model\ResourceModel;

class TaxCategory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('bananacode_taxcategory', 'entity_id');
	}
	
}