<?php

namespace Bananacode\ShoppingList\Model\ResourceModel;

class ShoppingList extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('bananacode_shoppinglist', 'list_id');
	}
	
}