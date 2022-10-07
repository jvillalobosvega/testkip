<?php

namespace Bananacode\Ls\Helper;

use Exception;
use \Ls\Core\Model\LSR;
use \Ls\Omni\Client\Ecommerce\Entity;
use \Ls\Omni\Client\Ecommerce\Entity\Enum\DocumentIdType;
use \Ls\Omni\Client\Ecommerce\Operation;
use \Ls\Omni\Client\ResponseInterface;
use \Ls\Omni\Exception\InvalidEnumException;
use Magento\Checkout\Model\Session\Proxy as CheckoutSessionProxy;
use Magento\Customer\Model\Session\Proxy as CustomerSessionProxy;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Class BasketHelper
 * @package Bananacode\Ls\Helper
 */
class BasketHelper extends \Ls\Omni\Helper\BasketHelper {

    /**
     * This function is overriding in hospitality module
     * @param Entity\OneList $oneList
     * @return Entity\OneListCalculateResponse|Entity\Order
     * @throws InvalidEnumException|NoSuchEntityException
     * @throws Exception
     */
    public function calculate(Entity\OneList $oneList)
    {
        // @codingStandardsIgnoreLine
        $storeId = $this->getDefaultWebStore();
        $cardId  = $oneList->getCardId();

        /** @var Entity\ArrayOfOneListItem $oneListItems */
        $oneListItems = $oneList->getItems();

        /** @var Entity\OneListCalculateResponse $response */
        $response = false;

        if (!($oneListItems->getOneListItem() == null)) {
            /** @var Entity\OneListItem || Entity\OneListItem[] $listItems */
            $listItems = $oneListItems->getOneListItem();

            if (!is_array($listItems)) {
                /** Entity\ArrayOfOneListItem $items */
                // @codingStandardsIgnoreLine
                $items = new Entity\ArrayOfOneListItem();
                $items->setOneListItem($listItems);
                $listItems = $items;
            }
            // @codingStandardsIgnoreStart
            $oneListRequest = (new Entity\OneList())
                ->setCardId($cardId)
                ->setListType(Entity\Enum\ListType::BASKET)
                ->setItems($listItems)
                ->setStoreId($storeId);

            /** @var Entity\OneListCalculate $entity */
            if ($this->getCouponCode() != "" and $this->getCouponCode() != null) {
                /*$offer  = new Entity\OneListPublishedOffer();
                $offers = new Entity\ArrayOfOneListPublishedOffer();
                $offers->setOneListPublishedOffer($offer);
                $offer->setId($this->getCouponCode());
                $offer->setType("Coupon");
                $oneListRequest->setPublishedOffers($offers);*/
                $oneListRequest->setPublishedOffers($this->_offers());
            } else {
                $oneListRequest->setPublishedOffers($this->_offers());
            }

            /**
             * @var $item \Ls\Omni\Client\Ecommerce\Entity\OneListItem
             */
            $itemsNoIVAFilter = [];
            foreach ($oneListRequest->getItems() as $item) {
                if($item->getItemId() != 'EXENTOIVA') {
                    $itemsNoIVAFilter[] = $item;
                } else {
                    $oneListRequest->setExento(true);
                }
            }
            $itemsNoIVA = new Entity\ArrayOfOneListItem();
            $itemsNoIVA->setOneListItem($itemsNoIVAFilter);
            $oneListRequest->setItems($itemsNoIVA);

            $entity = new Entity\OneListCalculate();
            $entity->setOneList($oneListRequest);
            $request = new Operation\OneListCalculate();
            // @codingStandardsIgnoreEnd

            /** @var  Entity\OneListCalculateResponse $response */
            $response = $request->execute($entity);
        }
        if (($response == null)) {
            // @codingStandardsIgnoreLine
            $oneListCalResponse = new Entity\OneListCalculateResponse();
            return $oneListCalResponse->getResult();
        }
        if (property_exists($response, "OneListCalculateResult")) {
            // @codingStandardsIgnoreLine
            $this->setOneListCalculationInCheckoutSession($response->getResult());
            return $response->getResult();
        }
        if (is_object($response)) {
            $this->setOneListCalculationInCheckoutSession($response->getResult());
            return $response->getResult();
        } else {
            return $response;
        }
    }
}
