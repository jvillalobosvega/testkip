<?php

namespace Bananacode\ShoppingList\Observer;

use Bananacode\ShoppingList\Model\ShoppingListFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CheckList
 * @package Bananacode\ShoppingList\Observer
 */
class CheckList implements ObserverInterface
{
    /**
     * @var ShoppingListFactory
     */
    protected $_shoppingList;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * CheckList constructor.
     * @param ShoppingListFactory $shoppingList
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        ShoppingListFactory $shoppingList,
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_shoppingList = $shoppingList;
        $this->_customer = $customer;
        $this->_redirect = $redirect;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        try {
            $future = $this->_redirect->getRedirectUrl();
            $future = explode('?', $future);
            $future = explode('&', $future[(count($future) - 1)]);
            $values = [];
            foreach ($future as $f) {
                $parameter = explode('=', $f);
                if (count($parameter) > 1) {
                    $values[$parameter[0]] = $parameter[1];
                }
            }

            /**
             * @var \Magento\Quote\Model\Quote\Item $item
             */
            $item = $observer->getEvent()->getQuoteItem();

            $event = $observer->getEvent()->getName();
            if (isset($values['sp']) && isset($values['q'])) {
                $shoppingList = $this->_shoppingList->create()->load($values['sp']);
                if ($shoppingList->getId()) {
                    $shoppingItems = explode(',', $shoppingList->getData()['items']);
                    foreach ($shoppingItems as &$shoppingItem) {
                        $shoppingItem = explode('^', $shoppingItem);
                        if (count($shoppingItem) > 1) {
                            similar_text(strtoupper($shoppingItem[0]), strtoupper($item->getProduct()->getName()), $percent2);
                            similar_text(strtoupper($shoppingItem[0]), strtoupper($values['q']), $percent1);
                            if (($percent1 > 99) && ($percent2 > 40)) {
                                if ($event === 'sales_quote_remove_item') {
                                    $shoppingItem[1] = 1;
                                } elseif ($event === 'sales_quote_add_item') {
                                    $shoppingItem[1] = 0;
                                }
                            } else {
                                if (($percent2 > 40) && $event === 'sales_quote_remove_item') {
                                    $shoppingItem[1] = 1;
                                }
                            }
                            $shoppingItem = implode('^', $shoppingItem);
                        }
                    }
                    $shoppingItems = implode(',', $shoppingItems);
                    $shoppingList->setData("items", $shoppingItems);
                    $shoppingList->save();
                }
            }
        } catch (\Exception $e) {

        }
    }
}
