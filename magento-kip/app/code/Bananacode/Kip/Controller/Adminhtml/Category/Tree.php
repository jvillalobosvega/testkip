<?php
namespace Bananacode\Kip\Controller\Adminhtml\Category;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Tree
 * @package Bananacode\Kip\Controller\Adminhtml\Category
 */
class Tree extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var WriterInterface
     */
    protected $_configWriter;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * Tree constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bananacode\Kip\Block\Main $kip
     * @param WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bananacode\Kip\Block\Main $kip,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        WriterInterface $configWriter
    ) {
        parent::__construct($context);
        $this->_configWriter = $configWriter;
        $this->_resourceConfig = $resourceConfig;
        $this->_kip = $kip;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bananacode_Kip::category');
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $recipesIds = '';
        $this->_kip->getRecipesIds($recipesIds);
        $recipesIds = explode(',', $recipesIds);
        $recipesIds = array_filter($recipesIds);
        $this->_configWriter->save('bananacode/categories/recipes_ids', json_encode(array_values($recipesIds)));

        $this->_configWriter->save('bananacode/categories/main_json', $this->_kip->getCategoryTreeJSON());

        $this->_kip->setIsRecipe(true);
        $this->_configWriter->save('bananacode/categories/recipes_json', $this->_kip->getCategoryTreeJSON());

        $this->_redirect('adminhtml/system_config/edit/section/bananacode');
    }
}
