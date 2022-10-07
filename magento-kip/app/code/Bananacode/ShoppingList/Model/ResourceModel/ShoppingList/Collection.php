<?php

namespace Bananacode\ShoppingList\Model\ResourceModel\ShoppingList;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'list_id';
	protected $_eventPrefix = 'bananacode_shoppinglist_collection';
	protected $_eventObject = 'shoppinglist_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Bananacode\ShoppingList\Model\ShoppingList', 'Bananacode\ShoppingList\Model\ResourceModel\ShoppingList');
	}

}