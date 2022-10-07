<?php
namespace Bananacode\Kip\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class CategoryPromotedProducts
 * @package Bananacode\Kip\Setup\Patch\Data
 */
class CategoryPromotedProducts implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * @var \Magento\Catalog\Setup\CategorySetupFactory
     */
    protected $_categorySetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $_setup;

    /**
     * CategoryIcon constructor.
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_categorySetupFactory = $categorySetupFactory;
        $this->_setup = $setup;
    }

    /**
     * @return DataPatchInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $setup = $this->_categorySetupFactory->create(['setup' => $this->_setup]);
        $setup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'promoted_products',
            [
                'type' => 'varchar',
                'label' => 'Promoted Products',
                'input' => 'text',
                'backend' => '',
                'required' => false,
                'sort_order' => 10,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
            ]
        );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
