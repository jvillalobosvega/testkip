<?php

namespace Bananacode\Kip\Plugin\Algolia;

use Magento\Framework\Message\MessageInterface;

/**
 *
 */
class ProductHelper
{
    /**
     * @var \Bananacode\Kip\Block\Main
     */
    private $_kip;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productLoader;

    /**
     * CreatePost constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Bananacode\Kip\Block\Main $kip,
        \Magento\Catalog\Api\ProductRepositoryInterface $productLoader
    )
    {
        $this->_kip = $kip;
        $this->_productLoader = $productLoader;
    }

    /**
     * @param \Algolia\AlgoliaSearch\Helper\Entity\ProductHelper $subject
     * @param $customData
     * @return mixed
     */
    public function afterGetObject(
        \Algolia\AlgoliaSearch\Helper\Entity\ProductHelper $subject,
        $customData
    )
    {
        
        try {
            if(isset($customData['objectID'])) {
                    $product = $this->_productLoader->getById($customData['objectID']);
                    // $customData['kip_parent_data'] = $this->_kip->getChildProducts($customData['objectID']);                            
                    // $customData['kip_parent_debug'] = json_encode($product);     
                    // $customData['kip_parent_debug_2'] = json_encode($customData['objectID']);     
                // if($customData['in_stock']==1){                
                    if($customData['type_id'] == 'grouped') {
                        $customData['kip_description'] = '<div data-flow="1" class="grouped-product description">' . $this->_kip->getRecipeProps($product) . '</div>';
                        $customData['kip_description_strip'] =  $this->_kip->getRecipeProps($product, false, false);
                        
                        $customData['kip_description_mobile'] = $this->_kip->getPresentacionMovil($product);
                        $customData['kip_description_strip_mobile'] = $this->_kip->getPresentacionMovil($product, false, [], false, false);
                    } else {
                        $customData['kip_description'] = '<div data-flow="2" class="product description">' . $this->_kip->getPresentacion($product) . '</div>';
                        $customData['kip_description_strip'] = $this->_kip->getPresentacion($product, false, [], false, false);

                        $customData['kip_description_mobile'] = $this->_kip->getPresentacionMovil($product);
                        $customData['kip_description_strip_mobile'] = $this->_kip->getPresentacionMovil($product, false, [], false, false);
                    }
    
                    $customData['kip_label'] = $this->_kip->getProductLabels($product);

                    $customData['kip_label_mobile'] = $this->_kip->getProductLabelsMobile($product);
    
                    $customData['kip_image'] = $this->_kip->getProductImageHtml(null, $product);
    
                    $customData['kip_image_url'] = $this->_kip->getProductImageHtml(null, $product, true);

                    $customData['kip_image_url_mobile'] = $this->_kip->getProductImageMobile(null, $product, true);
    
                    $customData['kip_min_qty'] = $this->_kip->getMinSale($product);
    
                    $customData['kip_um'] = $this->_kip->getUM($product);     
                    
                    $customData['kip_discount'] = $this->_kip->getUM($product);     

                    $customData['kip_um_mobile'] = $this->_kip->getUMMovil($product);     

                                           
            // }
        }
        // $customData['kip_parent_debug_3'] = json_encode($customData);     
        } catch (\Exception $e) {}

        return $customData;
    }
}
