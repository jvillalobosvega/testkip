<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\MaximumCouponDiscount\Model;

/**
 * Class RulesApplier
 * @package Magento\SalesRule\Model\Validator
 */
class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{
    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param mixed $couponCode
     * @return $this
     */
    protected function applyRule($item, $rule, $address, $couponCode)
    {   
        try {

            $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/kip.log');
            $this->_logger = new \Monolog\Logger('Kip');
            $this->_logger->pushHandler($logHandler);                        
                        
            $IsOnlyForFirstOrder = $rule->getIsRss();    
            
            $this->_logger->addInfo(print_r('Starting', true));

            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $dataHelper = $om->create(\MW\MaximumCouponDiscount\Helper\Data::class);
            $currencySymbol = $om->get(\Magento\Store\Model\StoreManagerInterface::class);
            // $messageManager = $om->create(\Magento\Framework\Message\ManagerInterface::class);
            
            $customerSession = $om->create("Magento\Customer\Model\Session");    
            $resource = $om->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $select     = $connection->select()->from($resource->getTableName('sales_order'), 'COUNT(*)')->where('customer_id=?', $customerSession->getCustomerId());
            $totalOrderCount = (int) $connection->fetchOne($select); 

            
        

            $discountData = $this->getDiscountData($item, $rule, $address);
            $maxDiscount = $rule->getMaxDiscount();
            $discountAmount = $rule->getDiscountAmount();
            $baseSubtotal = $address->getBaseSubtotal();
        
            if ($dataHelper->isEnabled() && $discountData->getAmount() > 0 && $maxDiscount > 0 && $rule->getSimpleAction() == 'by_percent') {
                $this->_logger->addInfo(print_r('Descuento percentil', true));
                $discountTotalAmount = $baseSubtotal * $discountAmount / 100;
                if ($discountTotalAmount >= $maxDiscount) {
                    $this->_logger->addInfo(print_r('Aplicando descuento', true));
                    $finalDiscountPercentage = $item->getRowTotal() * $maxDiscount / $baseSubtotal;
                    $discountData->setAmount($finalDiscountPercentage);
                    $discountData->setBaseAmount($finalDiscountPercentage);
                    /*$messageManager->addWarning(
                        __('Descuento máximo de %1 alcanzado.',
                        $currencySymbol->getStore()->getCurrentCurrencyCode() . ' ' . $maxDiscount)
                    );*/
                }
            }   
        
        if ($IsOnlyForFirstOrder ){
                $this->_logger->addInfo(print_r('El cupon es para clientes nuevos', true));                                                   
                $this->_logger->addInfo(print_r('cliente id: '.$customerSession->getCustomerId(), true));                
                $this->_logger->addInfo(print_r('ordenes totales: '.$totalOrderCount, true));
                $IsOnlyForFirstOrder = $rule->getIsRss();                                   
            if ($totalOrderCount==0 ){
                $this->_logger->addInfo(print_r($customerSession->getCustomerId().' SI es un cliente nuevo -> '.$totalOrderCount, true));  
                $this->setDiscountData($discountData, $item);
                $this->maintainAddressCouponCode($address, $rule, $couponCode);
                $this->addDiscountDescription($address, $rule);
            } 
            else{
                $this->_logger->addInfo(print_r($customerSession->getCustomerId().' NO es un cliente nuevo -> '.$totalOrderCount, true));  
            }                   
        }
        else{
            $this->_logger->addInfo(print_r('El cupon NO es para clientes nuevos aplicar normal', true));  
            $this->setDiscountData($discountData, $item);
            $this->maintainAddressCouponCode($address, $rule, $couponCode);
            $this->addDiscountDescription($address, $rule);
        }
        return $this;
    }    
    catch(Exception $e) {
        echo 'Message: ' .$e->getMessage();
        $this->_logger->addInfo(print_r($e->getMessage(), true));
    }
    }
}
             

