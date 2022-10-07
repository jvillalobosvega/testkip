<?php
namespace Bananacode\Kip\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Class AddressLatitude
 * @package Bananacode\Kip\Setup\Patch\Data
 */
class AddressLatitude implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'address_latitude';

    const ATTRIBUTE_TYPE = 'varchar';

    const ATTRIBUTE_INPUT = 'text';

    const ATTRIBUTE_LABEL = 'Latitude';

    const ATTRIBUTE_BACKEND = '';

    const REQUIRED = false;

    const FORMS = [
        'adminhtml_customer_address',
        'customer_address_edit',
        'customer_register_address',
        'customer_address',
    ];

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Config
     */
    private $_eavResourceConfig;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Telephone constructor.
     * @param ModuleDataSetupInterface $setup
     * @param Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Config $eavResourceConfig
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Config $eavResourceConfig,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->eavConfig = $eavConfig;
        $this->_eavResourceConfig = $eavResourceConfig;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @return DataPatchInterface|void
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY . '_address');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY . '_address', self::ATTRIBUTE_CODE, [
            'type' => self::ATTRIBUTE_TYPE,
            'label' =>  self::ATTRIBUTE_LABEL,
            'input' => self::ATTRIBUTE_INPUT,
            'required' => self::REQUIRED,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 90,
            'position' => 90,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY . '_address', self::ATTRIBUTE_CODE)
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => self::FORMS,
            ]);

        $attribute->save();
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
