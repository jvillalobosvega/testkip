<?php

/**
 * Created by PhpStorm.
 * User: pablogutierrez
 * Date: 2020-02-09
 * Time: 23:06
 */

namespace Bananacode\Kipping\Model;

use Bananacode\Kipping\Api\KippingInterface;
use Ls\Core\Model\LSR;
use Ls\Omni\Helper\BasketHelper;
use Ls\Omni\Helper\OrderHelper;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Class Kipping
 * @package Bananacode\Kipping\Model
 */
class Kipping implements KippingInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $_orderRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $_orderCollection;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customer;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    private $_orderHistoryFactory;

    /** @var BasketHelper */
    private $basketHelper;

    /** @var OrderHelper */
    private $orderHelper;

    /** @var Order $orderResourceModel */
    private $orderResourceModel;

    /**
     * @var LSR
     */
    private $lsr;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    private $_userContext;

    /**
     * @var \Monolog\Logger
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $_productResource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $_adminContext;

    /**
     * @var \Bananacode\Kip\Helper\Notify
     */
    protected $_bananaNotify;

    /**
     * Kipping constructor.
     * @param Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMask
     * @param \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask $quoteIdMaskResource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customer,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,

        BasketHelper $basketHelper,
        OrderHelper $orderHelper,
        Order $orderResourceModel,
        LSR $LSR,

        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,

        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,

        \Magento\Backend\App\Action\Context $adminContext,

        \Bananacode\Kip\Helper\Notify $bananaNotify
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderRepository = $orderRepository;
        $this->_orderCollection = $orderCollection;
        $this->_scopeConfig = $scopeConfig;
        $this->_customer = $customer;
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->basketHelper = $basketHelper;
        $this->orderHelper = $orderHelper;
        $this->orderResourceModel = $orderResourceModel;
        $this->lsr = $LSR;
        $this->_userContext = $userContext;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_storeManager = $storeManager;
        $this->_productResource = $productResource;
        $this->_eavConfig = $eavConfig;

        $this->_adminContext = $adminContext;

        $this->_bananaNotify = $bananaNotify;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/kipsales.log');
        $this->_logger = new \Monolog\Logger('KipSales');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Get available delivery schedules
     *
     * @return mixed
     */
    public function schedules()
    {
        $adminRequest = $this->_adminContext->getSession()->getSessionId() === $this->_adminContext->getRequest()->getParam('form_key');
        $frontendUser = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);
        if ($frontendUser || $adminRequest) {
            try {
                $todaySchedules = $this->today();
                $festivitySchedules = $this->festivity();

                $scheduledSettings = [
                    "drivers" => $this->getCoreConfig('_scheduled/drivers'),
                    "hours" => $this->getCoreConfig('_scheduled/hours'),
                    "capacity_x_hour" => $this->getCoreConfig('_scheduled/capacity'),

                    "days" => $this->getCoreConfig('_scheduled/days'),
                    "1" => $this->getCoreConfig('_scheduled/hours_l'),
                    "2" => $this->getCoreConfig('_scheduled/hours_k'),
                    "3" => $this->getCoreConfig('_scheduled/hours_m'),
                    "4" => $this->getCoreConfig('_scheduled/hours_j'),
                    "5" => $this->getCoreConfig('_scheduled/hours_v'),
                    "6" => $this->getCoreConfig('_scheduled/hours_s'),
                    "7" => $this->getCoreConfig('_scheduled/hours_d')
                ];
                $enabledDays = explode(',', $scheduledSettings['days']);

                $current = new \DateTime();
                $current->setTimezone(new \DateTimeZone("America/El_Salvador"));
                $current->setTime(0, 0);

                $availableDays = $todaySchedules[0];
                $availableHours = $todaySchedules[1];
                $iplus = 1; //tomorrow
                $max = 5;
                $processDay = intval($current->format('N')) + 1;
                if ($processDay > 7) {
                    $processDay = 1;
                }

                for ($i = 0; $i < $max; $i++) {
                    if ($processDay > 7) {
                        $processDay = 1;
                    }

                    $isDayEnabled = gettype(array_search($processDay, $enabledDays));
                    if (isset($scheduledSettings[$processDay])) {
                        if ($scheduledSettings[$processDay] && $isDayEnabled == 'integer') {
                            $next = new \DateTime();
                            $next->setTimezone(new \DateTimeZone("America/El_Salvador"));
                            $next->setTime(0, 0);
                            $next->add(new \DateInterval('P' . ($iplus) . 'D'));

                            setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
                            $range = $scheduledSettings["hours"];
                            $label = strftime('%d de %B (%A)', $next->getTimestamp());
                            $availableDays[] = [
                                'value' =>  strftime('%d%m%y', $next->getTimestamp()),
                                'label' => $this->spanishDate($label),
                                'class' => ''
                            ];

                            $hoursArray = explode(',', $scheduledSettings[$processDay]);
                            $availableHours[strftime('%d%m%y', $next->getTimestamp())] = [
                                'hours_range' => $range,
                                'hours' => implode(',', $hoursArray),
                                'hoursArray' => $hoursArray
                            ];
                        }
                    }

                    $iplus++;
                    $processDay++;
                }

                $this->checkHoursAvailability($availableHours, $scheduledSettings, 'kipping_scheduled');

                $this->jsonResponse([
                    "availableDays" => $availableDays,
                    "availableHours" => $availableHours,

                    "availableDaysFestivity" => $festivitySchedules[0],
                    "availableHoursFestivity" => $festivitySchedules[1],

                    "isScheduledAvailable" => $this->isScheduledAvailable($availableHours, $availableDays),
                    "isExpressAvailable" => $this->isExpressFlashAvailable(),
                    "isFlashAvailable" => $this->isExpressFlashAvailable('_flash'),
                    "isScheduledTodayAvailable" => $this->isScheduledTodayAvailable(),
                    "isScheduledFestivityAvailable" => $this->isScheduledFestivityAvailable(),
                ]);
            } catch (\Exception $e) {
                $this->jsonResponse([
                    "error" => $e->getMessage()
                ]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * @param $availableHours
     */
    public function isScheduledAvailable($availableHours, $availableDays)
    {
        if ($this->hasFestivityItems()) {
            return  false;
        }

        foreach ($availableDays as $availableDay) {
            if (isset($availableDay['class'])) {
                if ($availableDay['class'] == 'scheduled_today') {
                    continue;
                }
            }

            foreach ($availableHours as $availableHour) {
                foreach ($availableHour as $h) {
                    if (!str_contains('disabled', $h)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $type
     * @param null $quote
     * @return bool
     */
    public function isExpressFlashAvailable($type = '_express', $quote = null)
    {
        try {
            if ($this->hasFestivityItems()) {
                return false;
            }

            if (!$quote) {
                if (!$quote = $this->_checkoutSession->getQuote()) {
                    return false;
                }
            }

            $current = new \DateTime();
            $cts = $current->getTimestamp();

            //Check if current hour is open
            $openHours = $this->getCoreConfig($type . '/open_hours');
            $openHours = explode(',', $openHours);
            $current->setTimezone(new \DateTimeZone("America/El_Salvador"));
            if (!in_array($current->format('H'), $openHours)) {
                return false;
            }

            //Check only $type items
            $count = 0;
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                $count += $item->getTotalQty();
                if (!$this->isItemExpressFlash($item->getProduct(), $type)) {
                    return false;
                }
            }

            //Check available drivers
            $settings = [
                "drivers" => $this->getCoreConfig($type . '/drivers'),
                "hours" => $this->getCoreConfig($type . '/hours'),
                "capacity_x_hour" => $this->getCoreConfig($type . '/capacity'),
                "products" => $this->getCoreConfig($type . '/products_limit') ?? 0
            ];

            if ($count > intval($settings['products'])) {
                return false;
            }

            $future = new \DateTime();
            $future->add(new \DateInterval('PT' . $settings['hours'] . 'H'));

            $last24h = new \DateTime();
            $last24h->sub(new \DateInterval('PT24H'));

            $select = $this->_orderCollection->getConnection()->select();
            $select
                ->from(
                    ['orders' => $this->_orderCollection->getTable('sales_order')],
                    [
                        'orders.entity_id',
                        'orders.increment_id',
                        'orders.created_at',
                        'orders.customer_email',
                        'orders.status',
                        'orders.kipping_delivery',
                        'orders.coupon_code',
                        'orders.total_qty_ordered',
                        'orders.shipping_method',
                    ]
                )
                ->where('orders.shipping_method = ?', "kipping" . $type)
                ->where('orders.kipping_delivery >= "' . $last24h->format('Y-m-d H:i:s') . '" AND orders.kipping_delivery <= "' . $future->format('Y-m-d H:i:s') . '"')
                ->order('orders.kipping_delivery DESC');

            $pendingDrives = 0;
            $orders = $this->_orderCollection->getConnection()->fetchAll($select);
            foreach ($orders as $order) {
                if ($order['shipping_method'] == 'kipping' . $type) {
                    $from = \DateTime::createFromFormat('Y-m-d H:i:s', $order['kipping_delivery']);
                    $to = \DateTime::createFromFormat('Y-m-d H:i:s', $order['kipping_delivery']);
                    $to->add(new \DateInterval('PT' . $settings['hours'] . 'H'));
                    if (($from->getTimestamp() <= $cts) && ($cts <= $to->getTimestamp())) {
                        $pendingDrives++;
                    }
                }
            }

            $availableDrivers = (floatval($settings['capacity_x_hour']) * floatval($settings['drivers'])) * floatval($settings['hours']);
            $availableDrivers -= $pendingDrives;
            return $availableDrivers > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool|null
     */
    public function isFestivityAvailable()
    {
        try {
            if (!$this->getCoreConfig('_scheduled_festivity/active')) {
                return null;
            }

            if (!$this->_checkoutSession->getQuote()) {
                return false;
            }

            if (!$this->hasFestivityItems()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param null $quote
     * @return bool
     */
    public function hasFestivityItems($quote = null)
    {
        try {
            if (!$quote) {
                if (!$quote = $this->_checkoutSession->getQuote()) {
                    return false;
                }
            }

            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($this->isItemFestivity($item->getProduct())) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $_product
     * @return bool
     */
    private function isItemExpressFlash($_product, $type = '_express')
    {
        try {
            $store = $this->_storeManager->getStore();
            $envioValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio' . $type, $store->getId());
            if (is_array($envioValue) || !$envioValue) {
                return false;
            }

            $envio = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio' . $type);
            $envioOptions = $envio->getSource()->getAllOptions();
            foreach ($envioOptions as $option) {
                if ($envioValue === $option['value']) {
                    if (strtolower($option['label']) == 'no') {
                        return false;
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $_product
     * @return bool
     */
    private function isItemFestivity($_product)
    {
        try {
            $store = $this->_storeManager->getStore();
            $festValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_fest', $store->getId());
            if (is_array($festValue) || !$festValue) {
                return false;
            }
            $fest = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_fest');
            $festOptions = $fest->getSource()->getAllOptions();
            foreach ($festOptions as $option) {
                if ($festValue === $option['value']) {
                    if (strtolower($option['label']) == 'no') {
                        return false;
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isScheduledTodayAvailable()
    {
        try {
            if ($this->hasFestivityItems()) {
                return  false;
            }

            $current = new \DateTime();
            $current->setTimezone(new \DateTimeZone("America/El_Salvador"));
            $todayHour = intval($current->format('G'));
            if (($todayHour + 1) > intval($this->getCoreConfig('_scheduled_today/hour'))) {
                return false;
            } else {
                $availability = $this->today();
                if (isset($availability[1])) {
                    $hours = array_values($availability[1]);
                    if (count($hours) > 0) {
                        $todayHours = $hours[0];
                        $available = false;
                        foreach ($todayHours['hoursArray'] as $h) {
                            if (strpos($h, 'disabled') === false) {
                                $available = true;
                            }
                        }
                        return $available;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * @return bool|null
     */
    public function isScheduledFestivityAvailable()
    {
        try {
            if (!$is = $this->isFestivityAvailable()) {
                return $is;
            }

            $availability = $this->festivity();
            if (isset($availability[1])) {
                $hours = array_values($availability[1]);
                if (count($hours) > 0) {
                    $festHours = $hours[0];
                    $available = false;
                    foreach ($festHours['hoursArray'] as $h) {
                        if (strpos($h, 'disabled') === false) {
                            $available = true;
                        }
                    }
                    return $available;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array[]|void
     */
    public function today()
    {
        try {
            $scheduleTodaySettings = [
                "limit" => $this->getCoreConfig('_scheduled_today/hour'),
                "drivers" => $this->getCoreConfig('_scheduled_today/drivers'),
                "hours" => $this->getCoreConfig('_scheduled_today/hours'),
                "capacity_x_hour" => $this->getCoreConfig('_scheduled_today/capacity'),

                "days" => $this->getCoreConfig('_scheduled_today/days'),
                "1" => $this->getCoreConfig('_scheduled_today/hours_l'),
                "2" => $this->getCoreConfig('_scheduled_today/hours_k'),
                "3" => $this->getCoreConfig('_scheduled_today/hours_m'),
                "4" => $this->getCoreConfig('_scheduled_today/hours_j'),
                "5" => $this->getCoreConfig('_scheduled_today/hours_v'),
                "6" => $this->getCoreConfig('_scheduled_today/hours_s'),
                "7" => $this->getCoreConfig('_scheduled_today/hours_d')
            ];
            $enabledDays = explode(',', $scheduleTodaySettings['days']);

            $current = new \DateTime();
            $current->setTimezone(new \DateTimeZone("America/El_Salvador"));
            $current->setTime(0, 0);
            $today = intval($current->format('N'));

            $availableDays = [];
            $availableHours = [];
            $processDay = $today;
            if ($processDay > 7) {
                $processDay = 1;
            }

            $isDayEnabled = gettype(array_search($processDay, $enabledDays));
            if (isset($scheduleTodaySettings[$processDay])) {
                if ($scheduleTodaySettings[$processDay] && $isDayEnabled == 'integer') {
                    $next = new \DateTime();
                    $next->setTimezone(new \DateTimeZone("America/El_Salvador"));
                    $next->setTime(0, 0);
                    $next->add(new \DateInterval('P' . 0 . 'D'));

                    setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
                    $range = $scheduleTodaySettings["hours"];
                    $label = strftime('%d de %B (%A)', $next->getTimestamp());
                    $availableDays[] = [
                        'value' =>  strftime('%d%m%y', $next->getTimestamp()),
                        'label' => $this->spanishDate($label),
                        'class' => 'scheduled_today'
                    ];

                    $hoursArray = explode(',', $scheduleTodaySettings[$processDay]);
                    $availableHours[strftime('%d%m%y', $next->getTimestamp())] = [
                        'hours_range' => $range,
                        'hours' => implode(',', $hoursArray),
                        'hoursArray' => $hoursArray
                    ];
                }
            }

            $this->checkHoursAvailability($availableHours, $scheduleTodaySettings, 'kipping_scheduled_today', 'PT0H');

            return [$availableDays, $availableHours];
        } catch (\Exception $e) {
            return [[], []];
        }
    }

    /**
     * @return array[]|void
     */
    public function festivity()
    {
        try {
            $scheduleFestivitySettings = [
                "limit" => $this->getCoreConfig('_scheduled_festivity/hour'),
                "drivers" => $this->getCoreConfig('_scheduled_festivity/drivers'),
                "hours" => $this->getCoreConfig('_scheduled_festivity/hours'),
                "capacity_x_hour" => $this->getCoreConfig('_scheduled_festivity/capacity'),
                "days_in_advance" => $this->getCoreConfig('_scheduled_festivity/days_in_advance'),
                "date_limit" => $this->getCoreConfig('_scheduled_festivity/date_limit'),

                "days" => $this->getCoreConfig('_scheduled_festivity/days'),
                "1" => $this->getCoreConfig('_scheduled_festivity/hours_l'),
                "2" => $this->getCoreConfig('_scheduled_festivity/hours_k'),
                "3" => $this->getCoreConfig('_scheduled_festivity/hours_m'),
                "4" => $this->getCoreConfig('_scheduled_festivity/hours_j'),
                "5" => $this->getCoreConfig('_scheduled_festivity/hours_v'),
                "6" => $this->getCoreConfig('_scheduled_festivity/hours_s'),
                "7" => $this->getCoreConfig('_scheduled_festivity/hours_d')
            ];
            $enabledDays = explode(',', $scheduleFestivitySettings['days']);

            $current = new \DateTime();
            $current->setTimezone(new \DateTimeZone("America/El_Salvador"));
            $current->setTime(0, 0);

            $availableDays = [];
            $availableHours = [];
            $iplus = !empty($scheduleFestivitySettings['days_in_advance']) ? intval($scheduleFestivitySettings['days_in_advance']) : 1;
            $max = 365; //one year
            $processDay = intval($current->format('N')) + 1;
            if ($processDay > 7) {
                $processDay = 1;
            }

            for ($i = 0; $i < $max; $i++) {
                if ($processDay > 7) {
                    $processDay = 1;
                }

                $isDayEnabled = gettype(array_search($processDay, $enabledDays));
                $dateLimit = explode('/', $scheduleFestivitySettings['date_limit']);
                $limit = new \DateTime();
                $limit->setTimezone(new \DateTimeZone("America/El_Salvador"));
                $limit->setTime(0, 0);
                $limit->setDate($dateLimit[2], $dateLimit[1], $dateLimit[0]);
                if (isset($scheduleFestivitySettings[$processDay])) {
                    if ($scheduleFestivitySettings[$processDay] && $isDayEnabled == 'integer') {
                        $next = new \DateTime();
                        $next->setTimezone(new \DateTimeZone("America/El_Salvador"));
                        $next->setTime(0, 0);
                        $next->add(new \DateInterval('P' . ($iplus) . 'D'));

                        if ($next->getTimestamp() < $limit->getTimestamp()) {
                            setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
                            $range = $scheduleFestivitySettings["hours"];
                            $label = strftime('%d de %B (%A)', $next->getTimestamp());
                            $availableDays[] = [
                                'value' =>  strftime('%d%m%y', $next->getTimestamp()),
                                'label' => $this->spanishDate($label),
                                'class' => ''
                            ];

                            $hoursArray = explode(',', $scheduleFestivitySettings[$processDay]);
                            $availableHours[strftime('%d%m%y', $next->getTimestamp())] = [
                                'hours_range' => $range,
                                'hours' => implode(',', $hoursArray),
                                'hoursArray' => $hoursArray
                            ];
                        } else {
                            $i = 365;
                        }
                    }
                }

                $iplus++;
                $processDay++;
            }

            $this->checkHoursAvailability($availableHours, $scheduleFestivitySettings, 'kipping_scheduled_festivity');

            return [$availableDays, $availableHours];
        } catch (\Exception $e) {
            return [[], []];
        }
    }

    /**
     * @param $availableHours
     * @param $settings
     * @param $method
     */
    private function checkHoursAvailability(&$availableHours, $settings, $method, $fromAdd = 'PT24H')
    {
        $from = new \DateTime();
        $from->setTimezone(new \DateTimeZone("America/El_Salvador"));
        $from->setTime(0, 0);
        $from->add(new \DateInterval($fromAdd));

        $to = new \DateTime();
        $to->setTimezone(new \DateTimeZone("America/El_Salvador"));
        $to->setTime(0, 0);
        $to->add(new \DateInterval('P7D'));

        $select = $this->_orderCollection->getConnection()->select();
        $select->from(['orders' => $this->_orderCollection->getTable('sales_order')], [
            'orders.entity_id',
            'orders.increment_id',
            'orders.created_at',
            'orders.customer_email',
            'orders.status',
            'orders.kipping_delivery',
            'orders.shipping_method',
        ])
            ->where('orders.shipping_method= ?', $method)
            ->where('orders.kipping_delivery >= "' . $from->format('Y-m-d H:i:s') . '" AND orders.kipping_delivery <= "' . $to->format('Y-m-d H:i:s') . '"')
            ->order('orders.entity_id DESC');

        $orders = $this->_orderCollection->getConnection()->fetchAll($select);
        $availableDrivers = (floatval($settings['capacity_x_hour']) * floatval($settings['drivers'])) * floatval($settings['hours']);
        foreach ($orders as $order) {
            if ($order['shipping_method'] == $method) {
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $order['kipping_delivery']);
                $date->sub(new \DateInterval('PT6H')); // GMT-06:00
                $dateCode = strftime('%d%m%y', $date->getTimestamp());
                if (isset($availableHours[$dateCode])) {
                    $isHourOrdered = gettype(array_search($date->format('H'), explode(",", $availableHours[$dateCode]['hours'])));
                    if ($isHourOrdered == 'integer') {
                        if (!isset($availableHours[$dateCode]['pending'][$date->format('H')])) {
                            $availableHours[$dateCode]['pending'][$date->format('H')] = 1;
                        } else {
                            $availableHours[$dateCode]['pending'][$date->format('H')] += 1;
                        }
                    }
                }
            }
        }

        foreach ($availableHours as &$availableHour) {
            if (isset($availableHour['pending'])) {
                foreach ($availableHour['pending'] as $hour => $pending) {
                    if (($availableDrivers - $pending) < 1) {
                        $key = array_search(strval($hour), $availableHour['hoursArray']);
                        if (gettype($key) === 'integer') {
                            //unset($availableHour['hoursArray'][$key]);
                            $availableHour['hoursArray'][$key] .= ':disabled';
                            $availableHour['hours'] = implode(',', $availableHour['hoursArray']);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $date
     */
    private function spanishDate($date)
    {
        $months = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre'
        ];

        $days = [
            "Sunday" => "Domingo",
            "Monday" => "Lunes",
            "Tuesday" => "Martes",
            "Wednesday" => "Miércoles",
            "Thursday" => "Jueves",
            "Friday" => "Viernes",
            "Saturday" => "Sábado"
        ];

        foreach ($months as $english => $spanish) {
            $date = str_replace($english, $spanish, $date);
        }

        foreach ($days as $english => $spanish) {
            $date = str_replace($english, $spanish, $date);
        }

        return $date;
    }

    /**
     * Set custom kipping data on session
     *
     * @param string $data
     * @return mixed
     */
    public function session($data)
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            $this->_logger->addInfo(print_r('Sesión cliente ' . $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false) . ': ', true));
            $this->_logger->addInfo(print_r($data, true));
            //$this->_checkoutSession->setKippingData($data);
            $this->jsonResponse(json_decode($data, true));
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * Set custom kipping data on order
     *
     * @param string $data
     * @return mixed
     */
    public function comment($data)
    {
        $this->_logger->addInfo(print_r('Agregando comentarios data custom...', true));
        $this->_logger->addInfo(print_r($data, true));
        $adminRequest = $this->_adminContext->getSession()->getSessionId() === $this->_adminContext->getRequest()->getParam('form_key');
        $frontendUser = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);
        if ($frontendUser || $adminRequest) {
            $dataString = $data;
            $data = json_decode($data, true);

            $orderId = null;
            if (isset($data['order_id'])) {
                $orderId = $data['order_id'];
            }

            /** @var $order \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order */
            $order = $this->getOrder($orderId, $adminRequest);
            if ($order) {
                $this->_logger->addInfo(print_r('Orden #' . $order->getId(), true));

                $this->_bananaNotify->discord('sales', 'Datos Kip orden #' . $dataString, 'Nueva Orden');
                /*  MODIFICACION DE BOT */
                /* -- EMOJIS START */
                $manage = json_decode($dataString, true);
                $fecha = ":date: : sin fecha";
                $hora = ":clock: : sin hora";
                $envio = ":auto_rickshaw: : sin envío";
                $empaque = ":package: : sin empaque";
                $pago = ":credit_card: N/D";
                $nota = ":notepad_spiral: Nota: ";
                $documento = ":receipt: Tipo de documento: ";


                try {
                    // $this->_bananaNotify->discord('sales',  $dataString, 'Nueva Orden');                
                    $nota = ":notepad_spiral: Nota: " . $manage["order_note"];
                } catch (\Exception $e) {
                }
                try {
                    // $this->_bananaNotify->discord('sales',  $dataString, 'Nueva Orden');                
                    $documento = ":receipt: Tipo de documento: " . $manage["invoice_document"];
                } catch (\Exception $e) {
                }

             

                try {
                    // print("Fecha:".$manage["scheduled_day"]);
                    $crude = $manage["scheduled_day"];
                    if ($crude != "null") {
                        $day = substr($crude, 0, 2);
                        $month = substr($crude, 2, 2);
                        $year = substr($crude, 4, 6);
                        $fecha = ":date: Fecha de envío: " . $day . "/" . $month . "/" . $year;
                    }
                } catch (\Exception $e) {
                    $fecha = ":date: Fecha de envío: " . $crude;
                }
                try {
                    $cx = $manage["scheduled_hour"];
                    if ($cx != "null") {
                        if ($cx > 12) {
                            $hora_a = $cx - 12;
                            $num_padded = sprintf("%02d", $hora_a);
                            $hora = ":clock" . $hora_a . ": Hora de envío: " . $num_padded . ':00PM';
                        } else {
                            $hora_a = $cx;
                            $num_padded = sprintf("%02d", $hora_a);
                            $hora = ":clock" . $hora_a . ": Hora de envío: " . $num_padded . ':00AM';
                        }
                    }
                } catch (\Exception $e) {
                    $hora = ":clock" . $hora_a . ": Hora de envío: " . $manage["scheduled_hour"];
                }

                try {
                    $cxs = $manage["method"];
                    if ($cxs != "null") {
                        if ($cxs == "scheduled") {
                            $envio = ":auto_rickshaw: Tipo de envío: Programado ";
                        }
                        if ($cxs == "scheduled_today") {
                            $envio = ":auto_rickshaw: Tipo de envío: Programado mismo día ";
                        }
                        if ($cxs == "express") {
                            $envio = ":auto_rickshaw: Tipo de envío: Express ";
                        }
                        if ($cxs == "flash") {
                            $envio = ":auto_rickshaw: Tipo de envío: Flash :checkered_flag:";
                        }
                    }
                } catch (\Exception $e) {
                }

                try {
                    // $cxs = $manage["package"]; OLD
                    $cxs = $manage["package_method"];
                    if ($cxs != "null") {
                        if ($cxs == "no-package") {
                            $empaque = ":package: Tipo de empaque: Jaba ";
                        }
                        if ($cxs != "no-package") {
                            $empaque = ":package: Tipo de empaque: Bolsas plásticas ";
                        }
                    }
                } catch (\Exception $e) {
                }

                try {
                    $cxs = $manage["bin"];
                    if ($cxs != "null" || $cxs != "") {
                        $pago = ":credit_card: BIN de Pago: " . $cxs;
                    }
                } catch (\Exception $e) {
                }

                // print($fecha);
                // print($hora);
                // print($envio);
                // print($empaque);
                // print($pago);
                $this->_bananaNotify->discord('sales',  $fecha, 'Nueva Orden');
                $this->_bananaNotify->discord('sales',  $hora, 'Nueva Orden');
                $this->_bananaNotify->discord('sales',  $envio, 'Nueva Orden');
                $this->_bananaNotify->discord('sales',  $empaque, 'Nueva Orden');
                $this->_bananaNotify->discord('sales',  $pago, 'Nueva Orden');
                $this->_bananaNotify->discord('sales',  $nota, 'Nueva Orden');
                $this->_bananaNotify->discord('sales',  $documento, 'Nueva Orden');                
                /* -- -EMOJIS END */
                

                $address       = 'N/D';
                $shipToAddress = $order->getShippingAddress();
                if (!empty($shipToAddress)) {
                    // $address .= $order->getCustomerFirstname() ? $order->getCustomerFirstname() . '|' : '';
                    // $address .= $order->getCustomerLastname() ? $order->getCustomerLastname() . '|' : '';
                    $address .= isset($shipToAddress->getStreet()[0]) ? $shipToAddress->getStreet()[0] . ' | ' : '';
                    $address .= isset($shipToAddress->getStreet()[1]) ? $shipToAddress->getStreet()[1] . ' | ' : '';
                    $address .= isset($shipToAddress->getStreet()[2]) ? $shipToAddress->getStreet()[2] . ' | ' : '';
                    $address .= $shipToAddress->getCity() ? $shipToAddress->getCity() . ', ' : '';
                    $address .= $shipToAddress->getRegion() ? $shipToAddress->getRegion() . ', ' : '';
                    $address .= $shipToAddress->getPostCode() ? $shipToAddress->getPostCode() . ' ' : '';
                    $address .= $shipToAddress->getTelephone() ? $shipToAddress->getTelephone() . ' ' : '';
                    $this->_bananaNotify->discord('sales', ':triangular_flag_on_post: Dirección: ' . $address, 'Nueva Orden');
                } else {
                    $this->_bananaNotify->discord('sales', ':triangular_flag_on_post: Dirección: ' . $address, 'Nueva Orden');
                }

                /* CUPONES */
                $cupones = ":tickets: : N/D";                
                try {                                        
                    $this->_bananaNotify->discord('sales',  $cupones, 'Nueva Orden');                
                    if (str_contains($order['discount_description'],"REFERRALS")){
                        $this->_bananaNotify->discord('errors', ":tickets: :partying_face: Se ha utilzado el código de referidos ".$order['discount_description']." en la orden: ".$order['increment_id'].' :partying_face: ', 'CUPÓN DE REFERIDO');            
                        $this->_bananaNotify->discord('errors', ':tickets: :'.$order['discount_description'], 'CUPÓN DE REFERIDO');            
                        $this->_bananaNotify->discord('errors', ':money_with_wings: (Desc.) :'.$order['base_discount_amount'], 'CUPÓN DE REFERIDO');            
                        $this->_bananaNotify->discord('errors', ':busts_in_silhouette: :'.$order['customer_email'].'|'.$order['customer_firstname'].' '.$order['customer_lastname'], 'CUPÓN DE REFERIDO');            
                    }
                    // 
                } catch (\Exception $e) {                    
                    $this->_bananaNotify->discord('sales',  $cupones, 'Nueva Orden');    
                }                
                /* NOTIFICACION DE BOT FIN */



                //Package & Note
                try {
                    $this->_logger->addInfo(print_r('Empaque...', true));
                    if ($order->canComment()) {
                        foreach ($data as $property => $value) {
                            $comment = true;
                            $commentVisible = '';
                            $commentRaw = '';
                            switch ($property) {
                                case "package_method":
                                    if ($value == 'no-package') {
                                        $commentVisible = 'Sin empaquetado';
                                        $commentRaw = 'TipoPaquete:jaba';
                                    } else {
                                        $commentVisible = 'Bolsas Plásticas';
                                        $commentRaw = 'TipoPaquete:bolsas';
                                    }
                                    break;
                                case "order_note":
                                    $commentVisible = 'Nota: ' . $value;
                                    $commentRaw = 'CommentsH:' . $value;
                                    break;
                                default:
                                    $comment = false;
                            }

                            if ($comment) {
                                $history = $this->_orderHistoryFactory->create()
                                    ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                                    ->setComment(__($commentVisible))
                                    ->setIsCustomerNotified(false)
                                    ->setStatus($order->getStatus())
                                    ->setIsVisibleOnFront(true);
                                $order->addStatusHistory($history);

                                $history = $this->_orderHistoryFactory->create()
                                    ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                                    ->setComment(__($commentRaw))
                                    ->setIsCustomerNotified(false)
                                    ->setStatus($order->getStatus())
                                    ->setIsVisibleOnFront(false);
                                $order->addStatusHistory($history);
                            }
                        }
                        $this->_orderRepository->save($order);
                    }
                } catch (\Exception $e) {
                    $this->_logger->addError(print_r($e->getMessage(), true));
                }

                //Delivery
                try {
                    $this->_logger->addInfo(print_r('Fecha y Hora de entrega...', true));
                    if ($order->getShippingMethod() == 'kipping_scheduled' || $order->getShippingMethod() == 'kipping_scheduled_today'  || $order->getShippingMethod() == 'kipping_scheduled_festivity') {
                        //Format date (user selection)
                        if (isset($data['scheduled_day']) and isset($data['scheduled_hour'])) {
                            $date = \DateTime::createFromFormat('dmy H', $data['scheduled_day'] . ' ' . $data['scheduled_hour']);
                        } else {
                            $date = new \DateTime();
                        }

                        //Store comments (user selection)
                        $dateVisibleValue = $date->format('Y-m-d');
                        $dateVisible = 'Fecha de entrega: ' . $dateVisibleValue;
                        $hourVisible = 'Hora de entrega: ' . $date->format('H:i:s');

                        //Store comments (format to GMT so add +6)
                        $date->add(new \DateInterval('PT6H')); // GMT-06:00;
                        $hourRaw = 'HoraEntrega:' . $date->format('H:i:s');
                        $dateRaw = 'requestedDeliveryDate:' . $dateVisibleValue . 'T' . $date->format('H:i:s');

                        //Store on DB (format to GMT so add +6)
                        $deliveryDateTime = $date->format('Y-m-d H:i:s');
                    } else {
                        //Current date (GMT)
                        $current1 = new \DateTime();

                        //Store comments (format to  GMT-ES so remove -6)
                        $current1->setTimezone(new \DateTimeZone("America/El_Salvador"));
                        $dateVisibleValue = $current1->format('Y-m-d');
                        $dateVisible = 'Fecha de entrega: ' . $dateVisibleValue;
                        $hourVisible = 'Hora de entrega: ' . $current1->format('H:i:s');

                        //Current date (GMT)
                        $current2 = new \DateTime();

                        //Store comments (format GMT)
                        $hourRaw = 'HoraEntrega:' . $current2->format('H:i:s');
                        $dateRaw = 'requestedDeliveryDate:' . $dateVisibleValue . 'T' . $current2->format('H:i:s');

                        //Store current date on DB (format GMT)
                        $deliveryDateTime = $current2->format('Y-m-d H:i:s');
                    }

                    $this->_logger->addInfo(print_r($deliveryDateTime, true));
                    $order->setKippingDelivery($deliveryDateTime);

                    if ($order->canComment()) {
                        $history = $this->_orderHistoryFactory->create()
                            ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                            ->setComment(__($dateVisible))
                            ->setIsCustomerNotified(false)
                            ->setStatus($order->getStatus())
                            ->setIsVisibleOnFront(true);
                        $order->addStatusHistory($history);

                        $history = $this->_orderHistoryFactory->create()
                            ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                            ->setComment(__($dateRaw))
                            ->setIsCustomerNotified(false)
                            ->setStatus($order->getStatus())
                            ->setIsVisibleOnFront(false);
                        $order->addStatusHistory($history);

                        $history = $this->_orderHistoryFactory->create()
                            ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                            ->setComment(__($hourVisible))
                            ->setIsCustomerNotified(false)
                            ->setStatus($order->getStatus())
                            ->setIsVisibleOnFront(true);
                        $order->addStatusHistory($history);

                        $history = $this->_orderHistoryFactory->create()
                            ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                            ->setComment(__($hourRaw))
                            ->setIsCustomerNotified(false)
                            ->setStatus($order->getStatus())
                            ->setIsVisibleOnFront(false);
                        $order->addStatusHistory($history);
                    }

                    $this->_orderRepository->save($order);
                } catch (\Exception $e) {
                    $this->_logger->addError(print_r($e->getMessage(), true));
                }

                //Tax
                try {
                    $this->_logger->addInfo(print_r('Documento fiscal...', true));
                    if ($order->canComment()) {
                        if (isset($data['invoice_document'])) {
                            $history = $this->_orderHistoryFactory->create()
                                ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                                ->setComment(__('invoice_document:' . $data['invoice_document']))
                                ->setIsCustomerNotified(false)
                                ->setStatus($order->getStatus())
                                ->setIsVisibleOnFront(false);
                            $order->addStatusHistory($history);
                            $this->_orderRepository->save($order);
                        }
                    }
                } catch (\Exception $e) {
                    $this->_logger->addError(print_r($e->getMessage(), true));
                }

                //BIN
                try {
                    $this->_logger->addInfo(print_r('BIN...', true));
                    if ($order->canComment()) {
                        if (isset($data['bin'])) {
                            $history = $this->_orderHistoryFactory->create()
                                ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                                ->setComment(__('bin:' . $data['bin']))
                                ->setIsCustomerNotified(false)
                                ->setStatus($order->getStatus())
                                ->setIsVisibleOnFront(false);
                            $order->addStatusHistory($history);
                            $this->_orderRepository->save($order);
                        }
                    }
                } catch (\Exception $e) {
                    $this->_logger->addError(print_r($e->getMessage(), true));
                }

                //Send order to LS
                $this->_logger->addInfo(print_r('Enviar orden a LS...', true));
                $this->placeLsOrder($order, $adminRequest);
            }
        } else {
            $this->_logger->addInfo(print_r('Usuario no registrado...', true));
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * @param $config
     * @return mixed
     */
    private function getCoreConfig($config)
    {
        return $this->_scopeConfig->getValue(
            'carriers/kipping' . $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $response
     */
    private function jsonResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    /**
     * @param null $orderId
     * @param false $adminRequest
     * @return false|\Magento\Sales\Model\Order|mixed
     */
    private function getOrder($orderId = null, $adminRequest = false)
    {
        if ($orderId) {
            if ($adminRequest) {
                $this->_bananaNotify->discord('sales', 'Orden #' . $orderId . ' llegando desde el **ADMIN**', 'Nueva Orden');
                $this->_logger->addInfo(print_r('Order from ADMIN...', true));
            } else {
                $this->_bananaNotify->discord('sales', 'Orden #' . $orderId . ' llegando desde el **APP**', 'Nueva Orden');
                $this->_logger->addInfo(print_r('Order from APP...', true));
            }
            $searchCriteria =
                $this->_searchCriteriaBuilder
                ->addFilter('increment_id', $orderId)
                ->create();
            $orderList = $this->_orderRepository->getList($searchCriteria)->getItems();
            return reset($orderList);
        } else {
            return $this->_checkoutSession->getLastRealOrder();
        }
    }

    /**
     * @param $order
     * @param false $isAdmin
     */
    public function placeLsOrder($order, $isAdmin = false)
    {
        $time_start = microtime(true);

        try {
            $documentId = NULL;
            $check = false;

            if ($isAdmin) {
                $oneListCalculation = $this->basketHelper->getOneListAdmin(
                    $order->getCustomerEmail(),
                    $order->getStore()->getWebsiteId(),
                    $order->getCustomerIsGuest()
                );
            } else {
                $oneListCalculation = $this->basketHelper->calculateOneListFromOrder($order);
                //$oneListCalculation = $this->basketHelper->getOneListCalculationFromCheckoutSession();
            }

            /*if(empty($oneListCalculation) && !$isAdmin) {
                $oneListCalculation = $this->basketHelper->calculateOneListFromOrder($order);
            }*/

            if (empty($order->getIncrementId())) {
                $this->_logger->addInfo(print_r('No increment order id....', true));
                $this->jsonResponse([
                    "success" => false
                ]);
            }

            if ($this->lsr->isLSR($this->lsr->getCurrentStoreId())) {
                $paymentMethod = $order->getPayment();
                if (!empty($paymentMethod)) {
                    $paymentMethod = $order->getPayment()->getMethodInstance();
                    $transId       = $order->getPayment()->getLastTransId();
                    $check         = $paymentMethod->isOffline();
                    if ($paymentMethod->getCode() === 'free') {
                        $check = true;
                    }
                }

                if (!empty($oneListCalculation)) {
                    if (($check == true || !empty($transId))) {
                        $request  = $this->orderHelper->prepareOrder($order, $oneListCalculation);
                        $response = $this->orderHelper->placeOrder($request);
                        try {
                            if ($response) {
                                $documentId = $response->getResult()->getId();
                                if (!empty($documentId)) {
                                    $order->setDocumentId($documentId);
                                    $this->basketHelper->setLastDocumentIdInCheckoutSession($documentId);
                                }
                                $oneList = $this->basketHelper->getOneListFromCustomerSession();
                                if ($oneList) {
                                    $this->basketHelper->delete($oneList);
                                }
                                $order->addCommentToStatusHistory(
                                    __('Order request has been sent to LS Central successfully %1', $documentId)
                                );
                                $order->addCommentToStatusHistory(
                                    __('LSDocumentID:%1', $documentId)
                                );
                                $this->orderResourceModel->save($order);

                                $this->_logger->addInfo(print_r((microtime(true) - $time_start) . 's', true));
                                $this->_logger->addInfo(print_r($documentId, true));
                                $this->_bananaNotify->discord('sales', '╰✧ Orden #' . $order->getIncrementId() . ' enviada a LS con el ID **' . $documentId . '** :point_left: --------------------------------', 'Nueva Orden');
                            } else {
                                $this->_logger->addInfo(print_r($response, true));
                                $this->_logger->addInfo(print_r('No ls response....', true));
                                $this->orderHelper->disasterRecoveryHandler($order);
                            }
                        } catch (\Exception $e) {
                            $this->_logger->addError(print_r($e->getMessage(), true));
                            $this->jsonResponse([
                                "success" => false
                            ]);
                        }
                        $this->basketHelper->unSetRequiredDataFromCustomerAndCheckoutSessions();
                    }
                } else {
                    $this->_logger->addInfo(print_r('No one list....', true));
                }
            } else {
                $this->orderHelper->disasterRecoveryHandler($order);
                $this->_logger->addInfo(print_r('No ls enabled....', true));
                $this->jsonResponse([
                    "success" => false
                ]);
            }
        } catch (\Exception $e) {
            $this->_logger->addError(print_r($e->getMessage(), true));
            $this->jsonResponse([
                "success" => false
            ]);
        }

        $this->jsonResponse([
            "success" => true,
            "message" => $documentId
        ]);
    }
}
