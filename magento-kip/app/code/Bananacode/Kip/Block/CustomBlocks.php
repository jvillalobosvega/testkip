<?php

namespace Bananacode\Kip\Block;

/**
 * Class CustomBlocks
 * @package Bananacode\Kip\Block
 */
class CustomBlocks extends \Magento\Framework\View\Element\Template
{
    const NAVBAR_BLOCK_ID = 'custom_navbar_links';

    const EMPTY_MINICART_BLOCK_ID = 'custom_empty_minicart';

    const EMPTY_WISHLIST_BLOCK_ID = 'custom_empty_wishlist';

    const EMPTY_RECURRING_BLOCK_ID = 'custom_empty_recurring';

    const FOOTER_BLOCK_ID = 'custom_footer_links';

    const CONTACT_BLOCK_ID = 'custom_contact';

    const LOYALTY_BLOCK_ID = 'custom_loyalty';

    const EMPTY_RECIPES_WISHES_BLOCK_ID = 'custom_recipes_nowishes';

    const OFFERS = 'category_offers';

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var Main
     */
    public $_kipMain;

    /**
     * Navbar constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Bananacode\Kip\Block\Main $kipMain
    ) {
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_kipMain = $kipMain;

        parent::__construct($context);
    }

    /**
     * @param $blockType
     * @return mixed|string
     */
    public function getHtmlBlock($blockType, $htmlOnly = false)
    {
        try {
            switch ($blockType) {
                case "footer":
                    $blockId = self::FOOTER_BLOCK_ID;
                    break;
                case "loyalty":
                    $blockId = self::LOYALTY_BLOCK_ID;
                    break;
                case "contact":
                    $blockId = self::CONTACT_BLOCK_ID;
                    break;
                case "navbar":
                    $blockId = self::NAVBAR_BLOCK_ID;
                    break;
                case "minicart-empty":
                    $blockId = self::EMPTY_MINICART_BLOCK_ID;
                    break;
                case "wishlist-empty":
                    $blockId = self::EMPTY_WISHLIST_BLOCK_ID;
                     break;
                case "recurring-empty":
                    $blockId = self::EMPTY_RECURRING_BLOCK_ID;
                    break;
                case "recipes-nowishes":
                    $blockId = self::EMPTY_RECIPES_WISHES_BLOCK_ID;
                    break;
                case "offers":
                    $blockId = self::OFFERS;
                    break;
                default: return "";
            }

            $block = $this->_blockFactory->create();
            $block->setStoreId($this->_storeManager->getStore()->getId())->load($blockId);

            if (!$block) {
                return '';
            }

            if (!$block->getContent()) {
                return '';
            }

            if($htmlOnly) {
                return $block->getContent();
            }

            $html = $this->_filterProvider->getBlockFilter()->setStoreId($this->_storeManager->getStore()->getId())->filter($block->getContent());

            if ($blockType == 'navbar') {
                $html = str_replace('<ul>', '', $html);
                $html = str_replace('</ul>', '', $html);
            }

            return $html;
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * @return bool
     */
    public function isHomePage() {
        return $this->getRequest()->getFullActionName() == 'cms_index_index';
    }
}
