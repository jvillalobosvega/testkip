<?php

namespace Bananacode\Kip\Block\Navigation;

/**
 * Class State
 * @package Bananacode\Kip\Block\Navigation
 */
class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /**
     * State constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bananacode\Kip\Model\Layer\Resolver $layerResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bananacode\Kip\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $data);
    }
}
