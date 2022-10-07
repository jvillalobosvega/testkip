<?php

namespace Bananacode\Kip\Model\Layer;

/**
 * Class Resolver
 * @package Bananacode\Kip\Model\Layer
 */
class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{
    /**
     * Resolver constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Bananacode\Kip\Model\Layer $layer
     * @param array $layersPool
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Bananacode\Kip\Model\Layer $layer,
        array $layersPool
    ) {
        $this->layer = $layer;
        parent::__construct($objectManager, $layersPool);
    }

    /**
     * @param string $layerType
     */
    public function create($layerType)
    {
    }
}
