<?php
namespace Bananacode\TaxDocument\Setup;

use Bananacode\TaxDocument\Model\TaxCategoryFactory;
use Bananacode\TaxDocument\Model\TaxDocumentTypeFactory;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Bananacode\TaxDocument\Model\TaxDocumentTypeFactory
     */
    protected $taxDocumentTypeFactory;

    /**
     * @var \Bananacode\TaxDocument\Model\TaxCategoryFactory
     */
    protected $taxCategoryFactory;

    /**
     * @param \Bananacode\TaxDocument\Model\TaxDocumentTypeFactory $taxDocumentTypeFactory
     * @param \Bananacode\TaxDocument\Model\TaxCategoryFactory $taxCategoryFactory
     */
    public function __construct(
        TaxDocumentTypeFactory $taxDocumentTypeFactory,
        TaxCategoryFactory $taxCategoryFactory
    ) {
        $this->taxDocumentTypeFactory = $taxDocumentTypeFactory;
        $this->taxCategoryFactory = $taxCategoryFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $category = $this->taxCategoryFactory->create();

        $category->addData([
            'name' => 'Otro'
        ])->save();

        $category = $this->taxCategoryFactory->create();

        $category->addData([
            'name' => 'Mediano'
        ])->save();

        $category = $this->taxCategoryFactory->create();

        $category->addData([
            'name' => 'Grande'
        ])->save();

        $documentType = $this->taxDocumentTypeFactory->create();

        $documentType->addData([
            'name' => 'CCF'
        ])->save();

        $documentType = $this->taxDocumentTypeFactory->create();

        $documentType->addData([
            'name' => 'EIVA'
        ])->save();

        $setup->endSetup();
    }
}
