<?php

namespace Bananacode\Kip\Model\Category;

/**
 * Class DataProvider
 * @package Bananacode\Kip\Model\Category
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();

        $fields['content'][] = 'icon';

        return $fields;
    }
}
