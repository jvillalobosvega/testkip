<?php

namespace Bananacode\Ls\Helper;

use Exception;
use \Ls\Core\Model\LSR;
use \Ls\Omni\Client\Ecommerce\Entity;
use \Ls\Omni\Client\Ecommerce\Operation;
use \Ls\Omni\Client\ResponseInterface;
use \Ls\Omni\Model\Cache\Type;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class LoyaltyHelper
 * @package Bananacode\Ls\Helper
 */
class LoyaltyHelper extends \Ls\Omni\Helper\LoyaltyHelper
{
    /**
     * Convert Point Rate into Values
     * @return float|Entity\GetPointRateResponse|ResponseInterface|null
     * @throws NoSuchEntityException
     */
    public function getPointRate()
    {
        $storeId  = $this->lsr->getCurrentStoreId();
        $response = null;
        if ($this->lsr->isLSR($storeId)) {
            $cacheId  = LSR::POINTRATE . $storeId;
            $response = $this->cacheHelper->getCachedContent($cacheId);
            if ($response || floatval($response) == 0) {
                return $response;
            }
            // @codingStandardsIgnoreStart
            $request = new Operation\GetPointRate();
            $entity  = new Entity\GetPointRate();
            // @codingStandardsIgnoreEnd
            try {
                $response = $request->execute($entity);
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
            }
            if (!empty($response)) {
                $this->cacheHelper->persistContentInCache(
                    $cacheId,
                    $response->getResult(),
                    [Type::CACHE_TAG],
                    86400
                );
                return $response->getResult();
            }
        }
        return $response;
    }
}
