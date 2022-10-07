<?php

namespace Bananacode\Kipping\Plugin\Block\Checkout;

/**
 * Class LayoutProcessor
 * @package Bananacode\Kipping\Plugin\Block\Checkout
 */
class LayoutProcessor
{
    protected $_SELECT_FIELD;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * LayoutProcessor constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_SELECT_FIELD = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => '',
                'customScope' => '',
            ],
            'dataScope' => '',
            'label' => __(''),
            'sortOrder' => '',
            'options' => [],
            'id' => '',
            'provider' => 'checkoutProvider',
            'validation' => ['required-entry' => true],
            'visible' => true,
        ];
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param $jsLayout
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
    {
        if (!$this->getConfig('carriers/kipping/active')) {
            return $jsLayout;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create("Magento\Customer\Model\Session");
        $customer = $customerSession->getCustomer();

        $jsLayout = $this->renderShippingCoords($jsLayout);

        $jsLayout = $this->renderShippingCustomUpdates($jsLayout, $customer);

        return $this->renderBillingCustomUpdates($jsLayout, $customer);
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    private function renderShippingCoords(&$jsLayout) {
        /**
         * Latitude input
         */
        $latitudeCode = 'address_latitude';
        $latitude = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $latitudeCode,
            'label' => 'Latitude',
            'provider' => 'checkoutProvider',
            'sortOrder' => 200,
            'validation' => [
                'required-entry' => true
            ],
            'options' => '',
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => '',
            'placeholder' => 'Latitud'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][$latitudeCode] = $latitude;

        /**
         * Longitude input
         */
        $longitude = $latitude;
        $longitudeCode = 'address_longitude';
        $longitude['dataScope'] = 'shippingAddress.custom_attributes' . '.' . $longitudeCode;
        $longitude['label'] = 'Longitude';
        $longitude['sortOrder'] = 200;
        $longitude['placeholder'] = 'Longitud';

        $jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][$longitudeCode] = $longitude;

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    private function renderShippingCustomUpdates(&$jsLayout, $customer) {

        if(isset($jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'])) {

            $shippingFields = $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

            //Company required
            if (isset($shippingFields['company'])) {
                $shippingFields['company']['validation'] = ['required-entry' => true];
            }

            //Load customer telephone
            if ($customer && isset($shippingFields['telephone'])) {
                $shippingFields['telephone']['value'] = $customer->getTelephone();
            }

            //Load regions
            if (isset($shippingFields['region'])) {
                $shippingFields['region'] = $this->_SELECT_FIELD;
                $shippingFields['region']['config']['customScope'] = 'shippingAddress';
                $shippingFields['region']['config']['id'] = 'region';
                $shippingFields['region']['id'] = 'region';
                $shippingFields['region']['dataScope'] = 'shippingAddress.region';
                $shippingFields['region']['label'] = __('Departamento');
                $shippingFields['region']['sortOrder'] = $shippingFields['region_id']['sortOrder'];
                $departments = explode(',', $this->getConfig('bananacode/checkout/departments'));
                foreach($departments as $department) {
                    $shippingFields['region']['options'][] = [
                        'value' => $department,
                        'label' => $department
                    ];
                }
            }

            //Load cities
            if (isset($shippingFields['city'])) {
                $sortCity = $shippingFields['city']['sortOrder'];
                $shippingFields['city'] = $this->_SELECT_FIELD;
                $shippingFields['city']['config']['customScope'] = 'shippingAddress';
                $shippingFields['city']['config']['id'] = 'city';
                $shippingFields['city']['id'] = 'city';
                $shippingFields['city']['dataScope'] = 'shippingAddress.city';
                $shippingFields['city']['label'] = __('Ciudad');
                $shippingFields['city']['sortOrder'] = $sortCity;
                $cities = explode(',', $this->getConfig('bananacode/checkout/cities'));
                foreach($cities as $city) {
                    $shippingFields['city']['options'][] = [
                        'value' => $city,
                        'label' => $city
                    ];
                }
            }

            //Postcode
            if (isset($shippingFields['postcode'])) {
                //$shippingFields['postcode']['validation'] = ['required-entry' => true, 'max_text_length' => 100];
            }

            //Streets
            if (isset($shippingFields['street']['children'])) {

                $streets = $shippingFields['street']['children'];

                //street1
                $streets[0]['validation'] = ['required-entry' => true, 'max_text_length' => 100];
                //street2
                $streets[count($streets) - 2]['placeholder'] = 'Nº de casa, apto, oficina u otro';
                $streets[count($streets) - 2]['validation'] = ['required-entry' => true, 'max_text_length' => 50];

                $shippingFields['street']['children'] = $streets;
            }

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;
        }

        return $jsLayout;
    }

    /**
     * @param $jsLayout
     * @return mixed
     */
    private function renderBillingCustomUpdates($jsLayout, $customer) {

        if(isset($jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'])) {

            $billingPayments = $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];

            foreach ($billingPayments as $code => &$billingPayment) {
                if(isset($billingPayment['children']['form-fields']['children'])) {
                    $billingFields = $billingPayment['children']['form-fields']['children'];

                    //Company required
                    if (isset($billingFields['company'])) {
                        $billingFields['company']['validation'] = ['required-entry' => true];
                    }

                    //Load cities
                    if (isset($billingFields['city'])) {
                        $sortCity = $billingFields['city']['sortOrder'];
                        $billingFields['city'] = $this->_SELECT_FIELD;
                        $billingFields['city']['config']['customScope'] = 'billingAddress';
                        $billingFields['city']['config']['id'] = 'city';
                        $billingFields['city']['id'] = 'city';
                        $billingFields['city']['dataScope'] = 'billingAddress' . str_replace('-form', '', $code) . '.city';
                        $billingFields['city']['label'] = __('Ciudad');
                        $billingFields['city']['sortOrder'] = $sortCity;
                        $billingFields['city']['options'] = [
                            [
                                'value' => 'Ciudad',
                                'label' => 'Ciudad'
                            ],
                            [
                                'value' => 'San Salvador',
                                'label' => 'San Salvador'
                            ],
                            [
                                'value' => 'Antiguo Cuscatlán',
                                'label' => 'Antiguo Cuscatlán'
                            ],
                            [
                                'value' => 'Santa Tecla',
                                'label' => 'Santa Tecla'
                            ],
                            [
                                'value' => 'Nuevo Cuscatlán',
                                'label' => 'Nuevo Cuscatlán'
                            ],
                            [
                                'value' => 'San José Villanueva',
                                'label' => 'San José Villanueva'
                            ],
                        ];
                    }

                    //Streets
                    if (isset($billingFields['street']['children'])) {
                        $streets = $billingFields['street']['children'];

                        //street1
                        $streets[0]['validation'] = ['required-entry' => true, 'max_text_length' => 100];
                        //street2
                        $streets[count($streets) - 2]['placeholder'] = 'Nº de casa, apto, oficina u otro';
                        $streets[count($streets) - 2]['validation'] = ['required-entry' => true, 'max_text_length' => 50];

                        $billingFields['street']['children'] = $streets;
                    }

                    $billingPayment['children']['form-fields']['children'] = $billingFields;
                }
            }

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'] = $billingPayments;
        }

        return $jsLayout;
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getConfig($config)
    {
        return $this->_scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
