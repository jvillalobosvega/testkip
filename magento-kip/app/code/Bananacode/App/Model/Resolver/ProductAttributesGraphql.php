<?php

namespace Bananacode\App\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class ProductAttributesGraphql
 * @package Bananacode\App\Model\Resolver
 */
class ProductAttributesGraphql implements ResolverInterface
{
    /**
     * @var DataProvider\ProductAttributesGraphql
     */
    private $_productAttributesGraphqlDataProvider;

    /**
     * ProductAttributesGraphql constructor.
     * @param DataProvider\ProductAttributesGraphql $productAttributesGraphqlDataProvider
     */
    public function __construct(
        DataProvider\ProductAttributesGraphql $productAttributesGraphqlDataProvider
    ) {
        $this->_productAttributesGraphqlDataProvider = $productAttributesGraphqlDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $sku = $this->getSku($args);

        $productsData = $this->_productAttributesGraphqlDataProvider->getAttributesBySku($sku);

        return $productsData;
    }

    /**
     * @param array $args
     * @return mixed
     * @throws GraphQlInputException
     */
    private function getSku(array $args)
    {
        if (!isset($args['sku'])) {
            throw new GraphQlInputException(__('"SKU should be specified.'));
        }
        return $args['sku'];
    }
}
