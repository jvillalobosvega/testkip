<?php

namespace Bananacode\Ls\Block\Loyalty;

use Exception;
use \Ls\Core\Model\LSR;
use \Ls\Omni\Client\Ecommerce\Entity\Enum\LineType;
use \Ls\Omni\Client\Ecommerce\Entity\Enum\OfferDiscountLineType;
use \Ls\Omni\Client\Ecommerce\Entity\ImageView;
use \Ls\Omni\Client\Ecommerce\Entity\PublishedOffer;
use \Ls\Omni\Helper\LoyaltyHelper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Offers
 * @package Ls\Customer\Block\Loyalty
 */
class Offers extends \Ls\Customer\Block\Loyalty\Offers
{
    /**
     * @param $offerLines
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function getOfferProductCategoryLink($offerLines)
    {
        $type = null;
        $entity = null;
        $url = null;
        $returnEmpty = [$type, $entity, $url];
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $categoryCollection */
        $categoryCollection = $om->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $categoryIds = [];
        $count = 0;
        foreach ($offerLines as $offerLine) {
            if ($offerLine->getLineType() == OfferDiscountLineType::ITEM) {
                try {
                    if(count($offerLines) > 1) {
                        $catIds = $this->productRepository->get($offerLine->getId())->getCategoryIds();
                        if (!empty($catIds)) {
                            if ($count == 0) {
                                $categoryIds = $catIds;
                            } else {
                                $categoryIds = array_intersect($catIds, $categoryIds);
                            }
                            $count++;
                        }
                    }
                } catch (Exception $e) {
                    return $returnEmpty;
                }

            }
            elseif ($offerLine->getLineType() == OfferDiscountLineType::ITEM_CATEGORY ||
                    $offerLine->getLineType() == OfferDiscountLineType::PRODUCT_GROUP ||
                    $offerLine->getLineType() == OfferDiscountLineType::SPECIAL_GROUP
            ) {
                /**
                 * @var $categories \Magento\Catalog\Model\ResourceModel\Category\Collection
                 */
                $categories = $categoryCollection
                    ->addAttributeToFilter(
                        'nav_id',
                        ['in' => [$offerLine->getId()]]
                    )->load();
                foreach ($categories as $category) {
                    $categoryIds[] = $category->getId();
                }
            } else {
                return $returnEmpty;
            }
        }

        if (!empty($categoryIds)) {
            $categoryIds = array_values($categoryIds);
            $entity = $this->categoryRepository->get($categoryIds[count($categoryIds) - 1]);
            $url = $this->categoryHelper->getCategoryUrl($entity);
            $type = 'category';
        } else {
            try {
                $entity = $this->productRepository->get($offerLines[0]->getId());
                $url = $entity->getProductUrl();
                $type = 'product';
            } catch (NoSuchEntityException $e) {
                return $returnEmpty;
            }
        }

        if ($entity) {
            return [$entity, $type, $url];
        } else {
            return $returnEmpty;
        }
    }
}
