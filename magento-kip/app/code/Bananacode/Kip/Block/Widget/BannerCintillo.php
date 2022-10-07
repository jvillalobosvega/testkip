<?php
namespace Bananacode\Kip\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class BannerCintillo extends Template implements BlockInterface {

    protected $_template = "widget/bannercintillo.phtml";

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaFolder() {
        $media_folder = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $media_folder;
    }
}
