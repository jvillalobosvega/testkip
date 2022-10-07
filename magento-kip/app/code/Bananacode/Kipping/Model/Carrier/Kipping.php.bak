<?php
/**
 * Created by PhpStorm.
 * User: pablogutierrez
 * Date: 2020-02-09
 * Time: 23:06
 */

namespace Bananacode\Kipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Kipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'kipping';

    /**
     * @var bool
     */
    protected $_isFixed = false;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $_orderCollection;

    /**
     * @var \Bananacode\Kipping\Model\Kipping
     */
    private $_kipping;

    /**
     * Kipping constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Bananacode\Kipping\Model\Kipping $kipping,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderCollection = $orderCollection;
        $this->_kipping = $kipping;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|\Magento\Shipping\Model\Rate\Result|null
     * @throws \Exception
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$quote = $this->_checkoutSession->getQuote()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        if ($this->getCoreConfig('_express/active')) {
            if ($this->_kipping->isExpressFlashAvailable('_express', $quote)) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle('Kip');
                $method->setMethod('express');
                $method->setMethodTitle($this->getCode('method', 'express'));
                $expressTotal = $this->getCoreConfig('_express/price');
                $method->setCost($expressTotal);
                $method->setPrice($expressTotal);
                $result->append($method);
            }
        }

        if ($this->getCoreConfig('_flash/active')) {
            if ($this->_kipping->isExpressFlashAvailable('_flash', $quote)) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle('Kip');
                $method->setMethod('flash');
                $method->setMethodTitle($this->getCode('method', 'flash'));
                $expressTotal = $this->getCoreConfig('_flash/price');
                $method->setCost($expressTotal);
                $method->setPrice($expressTotal);
                $result->append($method);
            }
        }

        if ($this->getCoreConfig('_scheduled/active')) {
            if(!$this->_kipping->hasFestivityItems()) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle('Kip');
                $method->setMethod('scheduled');
                $method->setMethodTitle($this->getCode('method', 'scheduled'));
                $scheduledTotal = $this->getCoreConfig('_scheduled/price');
                $scheduledFree = floatval($this->getCoreConfig('_scheduled/free'));
                if($scheduledFree > 0) {
                    if($quote->getSubtotal() >= $scheduledFree) {
                        $scheduledTotal = 0;
                    }
                }
                $method->setCost($scheduledTotal);
                $method->setPrice($scheduledTotal);
                $result->append($method);
            }
        }

        if ($this->getCoreConfig('_scheduled_today/active')) {
            if ($this->_kipping->isScheduledTodayAvailable()) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle('Kip');
                $method->setMethod('scheduled_today');
                $method->setMethodTitle($this->getCode('method', 'scheduled_today'));
                $scheduledTotal = $this->getCoreConfig('_scheduled_today/price');
                $scheduledFree = floatval($this->getCoreConfig('_scheduled_today/free'));
                if($scheduledFree > 0) {
                    if($quote->getSubtotal() >= $scheduledFree) {
                        $scheduledTotal = 0;
                    }
                }
                $method->setCost($scheduledTotal);
                $method->setPrice($scheduledTotal);
                $result->append($method);
            }
        }

        if ($this->getCoreConfig('_scheduled_add/active')) {
            if ($this->scheduledAddApplies($quote)) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle('Kip');
                $method->setMethod('scheduled_add');
                $method->setMethodTitle($this->getCode('method', 'scheduled_add'));
                $scheduledTotal = $this->getCoreConfig('_scheduled_add/price');
                $scheduledFree = floatval($this->getCoreConfig('_scheduled_add/free'));
                if($scheduledFree > 0) {
                    if($quote->getSubtotal() >= $scheduledFree) {
                        $scheduledTotal = 0;
                    }
                }
                $method->setCost($scheduledTotal);
                $method->setPrice($scheduledTotal);
                $result->append($method);
            }
        }

        if ($this->getCoreConfig('_scheduled_festivity/active')) {
            if ($this->_kipping->isFestivityAvailable()) {
                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                $method = $this->rateMethodFactory->create();
                $method->setCarrier($this->_code);
                $method->setCarrierTitle('Kip');
                $method->setMethod('scheduled_festivity');
                $method->setMethodTitle($this->getCode('method', 'scheduled_festivity'));
                $scheduledTotal = $this->getCoreConfig('_scheduled_festivity/price');
                $scheduledFree = floatval($this->getCoreConfig('_scheduled_festivity/free'));
                if($scheduledFree > 0) {
                    if($quote->getSubtotal() >= $scheduledFree) {
                        $scheduledTotal = 0;
                    }
                }
                $method->setCost($scheduledTotal);
                $method->setPrice($scheduledTotal);
                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getCoreConfig('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }
        return $arr;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return bool
     */
    private function scheduledAddApplies($quote)
    {
        try {
            $today = new \DateTime();
            $select = $this->_orderCollection->getConnection()->select();
            $select
                ->from(
                    ['orders' => $this->_orderCollection->getTable('sales_order')],
                    ['orders.kipping_delivery']
                )
                ->where('orders.shipping_method LIKE ?', "%kipping_scheduled%")
                ->where('orders.customer_id = ?', $quote->getCustomerId())
                ->where('orders.kipping_delivery >= "' . $today->format('Y-m-d H:i:s') . '"');

            $orders = $this->_orderCollection->getConnection()->fetchAll($select);

            return count($orders) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'express' => $this->getCoreConfig('_express/title'),
                'flash' => $this->getCoreConfig('_flash/title'),
                'scheduled' => $this->getCoreConfig('_scheduled/title'),
                'scheduled_today' => $this->getCoreConfig('_scheduled_today/title'),
                'scheduled_add' => $this->getCoreConfig('_scheduled_add/title'),
                'scheduled_festivity' => $this->getCoreConfig('_scheduled_festivity/title')
            ]
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    private function getCoreConfig($config)
    {
        return $this->_scopeConfig->getValue(
            'carriers/kipping' . $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
