<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bananacode\App\Model;

use Magento\CatalogGraphQl\Model\Category\Hydrator;

use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Extract data from category tree
 */
class ExtractDataFromCategoryTree extends \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ExtractDataFromCategoryTree
{
    /**
     * Merge together complex categories trees
     *
     * @param array $tree1
     * @param array $tree2
     * @return array
     */
    private function mergeCategoriesTrees(array &$tree1, array &$tree2): array
    {
        //Patch
        if (isset($tree2['children']) && is_string($tree2['children'])) {
            unset($tree2['children']);
        }
        //----

        $mergedTree = $tree1;
        foreach ($tree2 as $currentKey => &$value) {
            if (is_array($value) && isset($mergedTree[$currentKey]) && is_array($mergedTree[$currentKey])) {
                $mergedTree[$currentKey] = $this->mergeCategoriesTrees($mergedTree[$currentKey], $value);
            } else {
                $mergedTree[$currentKey] = $value;
            }
        }
        return $mergedTree;
    }
}
