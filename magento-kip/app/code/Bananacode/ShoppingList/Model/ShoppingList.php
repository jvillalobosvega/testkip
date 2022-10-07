<?php

namespace Bananacode\ShoppingList\Model;

class ShoppingList extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'bananacode_shoppinglist_shoppinglist';

    protected $_cacheTag = 'bananacode_shoppinglist_shoppinglist';

    protected $_eventPrefix = 'bananacode_shoppinglist_shoppinglist';

    protected function _construct()
    {
        $this->_init('Bananacode\ShoppingList\Model\ResourceModel\ShoppingList');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
