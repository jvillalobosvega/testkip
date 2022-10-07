<?php

namespace Bananacode\Ls\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class LSR extends \Ls\Core\Model\LSR {

    /**
     * @param bool $store_id
     * @param bool $scope
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isLSR($store_id = false, $scope = false) {
        $disabledOrigins = [
            'QuoteRepositoryPlugin', 'LoyaltyHelper',
            'CartTotalRepository', 'Cart',
            'CouponCodeObserver', 'CartObserver', 'LoyaltyHelper'
        ];

        if(isset(debug_backtrace(2,1)[0]['file'])) {
            $origin =  debug_backtrace(2,1)[0]['file'];
            $origin = explode('/', $origin);
            $origin = $origin[count($origin) - 1];
            $origin = str_replace('.php', '', $origin);
            if(in_array($origin, $disabledOrigins)) {
                return false;
            }
        }

        if (isset($this->validateBaseUrlResponse)) {
            return $this->validateBaseUrlResponse;
        }

        if ($scope == 'website') {
            $baseUrl = $this->getWebsiteConfig(LSR::SC_SERVICE_BASE_URL, $store_id);
            $store   = $this->getWebsiteConfig(LSR::SC_SERVICE_STORE, $store_id);
        } else {
            $baseUrl = $this->getStoreConfig(LSR::SC_SERVICE_BASE_URL, $store_id);
            $store   = $this->getStoreConfig(LSR::SC_SERVICE_STORE, $store_id);
        }
        if (empty($baseUrl) || empty($store)) {
            $this->validateBaseUrlResponse = false;
        } else {
            $this->validateBaseUrlResponse = $this->validateBaseUrl($baseUrl);
        }

        return $this->validateBaseUrlResponse;
    }
}
