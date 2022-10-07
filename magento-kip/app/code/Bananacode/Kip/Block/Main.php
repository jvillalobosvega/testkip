<?php

namespace Bananacode\Kip\Block;

use Ls\Core\Model\LSR;
use Ls\Omni\Client\Ecommerce\Entity\ArrayOfSalesEntryLine;
use Ls\Omni\Client\Ecommerce\Entity\ArrayOfSalesEntryPayment;
use \Ls\Omni\Client\Ecommerce\Entity\Enum\DocumentIdType;
use Ls\Omni\Client\Ecommerce\Entity\SalesEntry;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class Main
 * @package Bananacode\Kip\Block
 */
class Main extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $_tokenFactory;

    /**
     * @var \Ls\Customer\Block\Account\Dashboard
     */
    protected $_lsAccount;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * @var \Magento\Category\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tree
     */
    protected $_adminCategoryTree;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $_layerResolver;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $_orderCollection;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    protected $_cartTotalRepository;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_context;

    /**
     * @var \Magento\Catalog\Api\CategoryManagementInterface
     */
    protected $_categoryManagement;

    /**
     * @var bool|string
     */
    protected $isRecipe;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $currentCat;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var \Ls\Omni\Helper\OrderHelper
     */
    protected $_omniOrderHelper;

    /**
     * @var \Ls\CustomerGraphQl\Helper\DataHelper
     */
    protected $_omniGraphHelper;

    /**
     * @var \Magento\Framework\GraphQl\Query\Resolver\ContextInterface
     */
    protected $_contextGraphQl;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $_configurable;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * Main constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
     * @param \Ls\Customer\Block\Account\Dashboard $lsAccount
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Block\Adminhtml\Category\Tree $adminCategoryTree
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $cartTotalRepository
     * @param \Magento\Catalog\Api\CategoryManagementInterface $categoryManagement
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Ls\Omni\Helper\OrderHelper $omniOrderHelper
     * @param \Ls\CustomerGraphQl\Helper\DataHelper $omniGraphHelper
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $contextGraphQl
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context                           $context,
        \Magento\Customer\Model\Session                                            $customer,
        \Magento\Integration\Model\Oauth\TokenFactory                              $tokenFactory,
        \Ls\Customer\Block\Account\Dashboard                                       $lsAccount,
        \Magento\Wishlist\Model\Wishlist                                           $wishlist,
        \Magento\Catalog\Model\CategoryRepository                                  $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory            $categoryCollectionFactory,
        \Magento\Catalog\Block\Adminhtml\Category\Tree                             $adminCategoryTree,
        \Magento\Catalog\Model\Layer\Resolver                                      $layerResolver,
        \Magento\Catalog\Model\ResourceModel\Product                               $productResource,
        \Magento\Catalog\Api\ProductRepositoryInterface                            $productRepository,
        \Magento\Catalog\Helper\Image                                              $imageHelper,
        \Magento\Catalog\Model\Product                                             $productModel,
        \Magento\Sales\Model\ResourceModel\Order\Collection                        $orderCollection,
        \Magento\Eav\Model\Config                                                  $eavConfig,
        \Magento\Framework\App\ResourceConnection                                  $resourceConnection,
        \Magento\Checkout\Model\Session                                            $checkoutSession,
        \Magento\Quote\Model\Cart\CartTotalRepository                              $cartTotalRepository,
        \Magento\Catalog\Api\CategoryManagementInterface                           $categoryManagement,
        \Magento\Sales\Model\OrderRepository                                       $orderRepository,
        \Ls\Omni\Helper\OrderHelper                                                $omniOrderHelper,
        \Ls\CustomerGraphQl\Helper\DataHelper                                      $omniGraphHelper,
        \Magento\Framework\GraphQl\Query\Resolver\ContextInterface                 $contextGraphQl,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory             $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility                                  $catalogProductVisibility,
        array                                                                      $data = []
    )
    {
        $this->_customer = $customer;
        $this->_tokenFactory = $tokenFactory;
        $this->_lsAccount = $lsAccount;
        $this->_wishlist = $wishlist;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_adminCategoryTree = $adminCategoryTree;
        $this->_categoryRepository = $categoryRepository;
        $this->_layerResolver = $layerResolver;
        $this->_productResource = $productResource;
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        $this->_productModel = $productModel;
        $this->_orderCollection = $orderCollection;
        $this->_eavConfig = $eavConfig;
        $this->_resourceConnection = $resourceConnection;
        $this->_checkoutSession = $checkoutSession;
        $this->_cartTotalRepository = $cartTotalRepository;
        $this->_context = $context;
        $this->_categoryManagement = $categoryManagement;
        $this->_orderRepository = $orderRepository;
        $this->_omniOrderHelper = $omniOrderHelper;
        $this->_omniGraphHelper = $omniGraphHelper;
        $this->_contextGraphQl = $contextGraphQl;
        $this->_configurable = $configurable;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        parent::__construct($context, $data);

        $this->currentCat = $this->_layerResolver->get()->getCurrentCategory();
        $this->isRecipe = $this->isRecipe();

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/kipcodity.log');
        $this->_logger = new \Monolog\Logger('Kip');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     *
     * CUSTOMER
     *
     */

    /**
     * @param $customerId
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getCustomerWishlist($customerId)
    {
        return $this->_wishlist->loadByCustomerId($customerId ?? $this->getCustomerId(), true);
    }

    /**
     * @return mixed
     */
    public function getCustomerExperienceData()
    {
        return $this->getCustomer()->getExperience();
    }

    /**
     * @return array
     */
    public function getDietStyleOptions()
    {
        return [
            "BBQ Lover",
            "Bebé",
            "Deporte",
            "Halal",
            "Kosher",
            "Libre de nueces",
            "Libre de sulfitos",
            "Light",
            "Mascota",
            "Meat Lover",
            "Natural",
            "Orgánico",
            "Para compartir",
            "Party",
            "Sin gluten",
            "Sin lactosa",
            "Sin Leche",
            "Sin trigo",
            "Vegano",
            "Vegetariano",
        ];
    }

    /**
     * @return array
     */
    public function getPromosOptions()
    {
        return [
            "Todas las ofertas",
            "Recetas",
            "Giveaway",
            "Rifas",
            "Ofertas",
            "Experiencias"
        ];
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->create("Magento\Customer\Model\Session");
        return $this->_customer->getCustomer();
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getCustomer()->getId();
    }

    /**
     * @return string|null
     */
    public function getCustomerName()
    {
        try {
            return $this->getCustomer()->getName();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getCustomerTelephone()
    {
        return $this->getCustomer()->getTelephone();
    }

    /**
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getCustomer()->getEmail();
    }

    /**
     * @param false $html
     * @return false|string
     */
    public function getCustomerAvatar($html = false)
    {
        if ($html && $this->getCustomer()->getAvatar()) {
            return '<img src="' . $this->getCustomer()->getAvatar() . '" alt="avatar"/>';
        }
        return $this->getCustomer()->getAvatar() ?? false;
    }

    /**
     * @return bool|\Ls\Omni\Client\Ecommerce\Entity\Account
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerLoyaltyData()
    {
        return $this->_lsAccount->getMembersInfo();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerLoyaltyLevel()
    {
        $level = '';
        if ($lsLoyaltyData = $this->getCustomerLoyaltyData()) {
            $level = $lsLoyaltyData->getScheme()->getDescription();
            //$level = 'Cronus Silver';
            if (strpos(strtoupper($level), 'BRONZE') !== false) {
                $level = 'bronze';
            } else {
                if (strpos(strtoupper($level), 'SILVER') !== false) {
                    $level = 'silver';
                } else {
                    if (strpos(strtoupper($level), 'GOLD') !== false) {
                        $level = 'gold';
                    } else {
                        if (strpos(strtoupper($level), 'PLATINUM') !== false) {
                            $level = 'platinum';
                        }
                    }
                }
            }
        }
        return $level;
    }

    /**
     * @param null $customer_id
     * @return string
     */
    public function getCustomerToken($customer_id = null)
    {
        $customerToken = $this->_tokenFactory->create();
        return $customerToken->createCustomerToken($customer_id ?? $this->getCustomer()->getId())->getToken();
    }

    /**
     *
     * PCP
     *
     */

    /**
     * @param null $tree
     * @param int $level
     * @param array $json
     * @param null $parent
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryTreeJSON($tree = null, $level = 0, &$json = [], $parent = null)
    {
        if (!$tree) {
            //Magento core tree
            $tree = $this->getMagCategoryTree();
        }

        $store = $this->_storeManager->getStore();
        $mediaSrc = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $level++;
        $validLevel = ($level === 2 || $level === 3);
        $noLastLevel = $level < 3;
        //$parentLevel = $level === 2;
        foreach ($tree as $branch) {
            $category = $this->_categoryRepository->get($branch->getId());
            if ($this->showCategory($category)) {
                $hasChildren = !empty($category->getChildren()) && (count(explode(',', $category->getChildren())) > 0);
                if ($hasChildren && $noLastLevel) {
                    if ($validLevel) {
                        $json[$category->getId()] = [
                            'icon' => (!empty($category->getIcon()) ? ($mediaSrc . str_replace('/media/', '', $category->getIcon())) : 'empty'),
                            'name' => $category->getName(),
                            'url' => $category->getUrl(),
                            'id' => $category->getId()
                        ];
                    }
                    $this->getCategoryTreeJSON($branch->getChildrenData(), $level, $json, $category);
                } else {
                    if ($validLevel) {
                        if ($level == 2) {
                            $json[$category->getId()] = [
                                'icon' => (!empty($category->getIcon()) ? ($mediaSrc . str_replace('/media/', '', $category->getIcon())) : 'empty'),
                                'name' => $category->getName(),
                                'url' => $category->getUrl(),
                                'id' => $category->getId(),
                                'children' => [
                                    [
                                        'icon' => 'empty',
                                        'name' => __('Ver todo'),
                                        'url' => $category->getUrl(),
                                        'id' => $category->getId()
                                    ]
                                ]
                            ];
                        } else {
                            if (!isset($json[$parent->getId()]['children'])) {
                                $json[$parent->getId()]['children'] = [];
                                $json[$parent->getId()]['children'][] = [
                                    'icon' => 'empty',
                                    'name' => __('Ver todo'),
                                    'url' => $parent->getUrl(),
                                    'id' => $parent->getId()
                                ];
                            }
                            $json[$parent->getId()]['children'][$category->getId()] = [
                                'icon' => (!empty($category->getIcon()) ? ($mediaSrc . str_replace('/media/', '', $category->getIcon())) : 'empty'),
                                'name' => $category->getName(),
                                'url' => $category->getUrl(),
                                'id' => $category->getId()
                            ];
                        }
                    }
                }
            }
        }
        return json_encode($json);
    }

    /**
     * @param null $root
     * @param string $ids
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRecipesIds(&$ids, $root = null)
    {
        if (!$root) {
            $root = $this->_categoryRepository->get($this->_scopeConfig->getValue('bananacode/recipes/category_id'));
        }
        $ids .= ',' . $root->getId() . ',';
        if (!empty($root->getChildren())) {
            $ids .= $root->getChildren();
            $children = explode(',', $root->getChildren());
            foreach ($children as $child) {
                if (!empty($child)) {
                    $category = $this->_categoryRepository->get($child);
                    $this->getRecipesIds($ids, $category);
                }
            }
        }
        return $ids;
    }

    /**
     * @param $tree \Magento\Catalog\Api\Data\CategoryTreeInterface[]
     * @param int $level
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryTreeHtml($jsonTree)
    {
        $html = "";
        foreach ($jsonTree as $category) {
            $category['icon'] = isset($category['icon']) ? "<img class='icon lazy-load-img' data-src='" . $category['icon'] . "'/>" : '';
            $hasChildren = isset($category['children']);
            if (isset($category['name']) && isset($category['url'])) {
                if ($hasChildren) {
                    $html .= "<div class='filter-options-item cat allow'>
                                <div class='filter-options-title'>
                                    " . $category['icon'] . "
                                    <span>" . $category['name'] . "</span>
                                </div>";

                    $html .= '<div data-role="content" class="filter-options-content">
                                    <ul>
                                        <li>';

                    $html .= $this->getCategoryTreeHtml($category['children']);

                    $html .= '    </li>
                                </ul>
                                   </div>';
                } else {
                    $active = '';
                    if ($this->currentCat) {
                        $active = ($category['id'] == $this->currentCat->getEntityId()) ? 'active' : '';
                    }
                    $html .= "<div class='filter-options-item cat allow no-collapse " . $active . "'>
                                <div class='filter-options-title'>
                                    " . $category['icon'] . "
                                    <a href='" . $category['url'] . "'>" . $category['name'] . "</a>
                                </div>";
                }
                $html .= "</div>";
            }
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getCategoryConfigJSON($main = false)
    {
        if ($this->isRecipe && !$main) {
            return $this->_scopeConfig->getValue('bananacode/categories/recipes_json');
        } else {
            return $this->_scopeConfig->getValue('bananacode/categories/main_json');
        }
    }

    /**
     * @param $currentCategoryId
     * @param $category
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isRecipe($currentCategoryId = null)
    {
        try {
            $children = json_decode($this->_scopeConfig->getValue('bananacode/categories/recipes_ids'), true);
            if (!$currentCategoryId && $this->currentCat) {
                $currentCategoryId = $this->currentCat->getId();
            }
            return in_array($currentCategoryId, $children);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array|false
     */
    public function getPromotedProducts()
    {
        try {
            $promoted = $this->currentCat->getCustomAttribute('promoted_products');
            if ($promoted) {
                $promotedArray = [
                    "1" => [
                        "type" => "Magento||CatalogWidget||Model||Rule||Condition||Combine",
                        "aggregator" => "any", "value" => "1", "new_child" => ""
                    ]
                ];
                $promotedProductArray = [
                    "type" => "Magento||CatalogWidget||Model||Rule||Condition||Product",
                    "attribute" => "sku",
                    "operator" => "==",
                    "value" => ""
                ];
                $products = explode(',', $promoted->getValue());
                foreach ($products as $i => $product) {
                    $promotedProductArray['value'] = $product;
                    $promotedArray['1--' . ($i + 1)] = $promotedProductArray;
                }
                return [
                    json_encode($promotedArray),
                    count($products)
                ];
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRecipes()
    {
        try {
            $store = $this->_storeManager->getStore();
            $mediaSrc = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $recipes = $this->_categoryRepository->get($this->_scopeConfig->getValue('bananacode/recipes/category_id'));
            $category = [];
            $category['description'] = $recipes->getDescription();
            $category['image'] = $mediaSrc . str_replace('/media/', '', $recipes->getImage());
            $category['icon'] = $recipes->getIcon();
            $category['url'] = $recipes->getUrl();
            $category['name'] = $recipes->getName();

            $recipesTree = $this->_categoryManagement->getTree($recipes->getId())->getChildrenData();
            $tabs = [];
            foreach ($recipesTree as $recipeBranch) {
                $recipeCategory = $this->_categoryRepository->get($recipeBranch->getId());
                $tabs[$recipeCategory->getId()] = $recipeCategory;
                $children = explode(',', $recipeCategory->getChildren());
                $childrenLoaded = [];
                foreach ($children as $child) {
                    if (!empty($child)) {
                        $childrenLoaded[] = $this->_categoryRepository->get($child);
                    }
                }
                $tabs[$recipeCategory->getId()]['children'] = $childrenLoaded;
            }

            return [
                "media" => $mediaSrc,
                "recipes" => $category,
                "tabs" => array_values($tabs)
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * @return string
     */
    public function getExpressShippingUrl()
    {
        $express = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_express');
        $expressOptions = $express->getSource()->getAllOptions();
        $yes = null;
        $no = null;
        $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        foreach ($expressOptions as $option) {
            if ($option['value']) {
                if (strtolower($option['label']) === 'no') {
                    $no = $option;
                } else {
                    $yes = $option;
                }
            }
        }

        if ($yes && $no) {
            /* Search page */
            if (strpos($url, 'catalogsearch') !== false) {
                if (strpos($url, 'ls_envio_express') !== false) {
                    return '<a onclick="((e) => {e.preventDefault(); this.classList.remove(`disable`)})()" class="disable" href="' . str_replace('&ls_envio_express=' . $no['value'], '', $url) . '">No</a>';
                } else {
                    return '<a onclick="((e) => {e.preventDefault(); this.classList.add(`disable`)})()" href="' . $url . '&ls_envio_express=' . $yes['value'] . '">Sí</a>';
                }
            } else {
                //Define current page url
                $baseUrl = $this->currentCat->getUrl();
                if ((strpos($url, 'kip/wishlist') !== false) || strpos($url, 'kip/recipes') !== false || strpos($url, 'kip/recurring') !== false) {
                    $baseUrl = $url;
                }

                //Check current parameters
                $u = explode('?', $baseUrl);
                if (count($u) > 1) {
                    $parameters = explode('&', $u[1]);
                    foreach ($parameters as $i => &$parameter) {
                        if (strpos($parameter, 'ls_envio_express') !== false) {
                            unset($parameter);
                            unset($parameters[$i]);
                        }
                    }
                } else {
                    $parameters = [];
                }

                //Re build url with parameters + express ls
                $expressYes = '?ls_envio_express=';
                if (count($parameters) > 0) {
                    $baseUrl = $u[0] . '?' . implode('&', $parameters);
                    $expressYes = '&ls_envio_express=';
                } else {
                    $baseUrl = $u[0];
                }

                /* PC & Custom Products page */
                if (strpos($url, 'ls_envio_express') !== false) {
                    return '<a onclick="((e) => {e.preventDefault(); this.classList.remove(`disable`)})()" class="disable" href="' . $baseUrl . '">No</a>';
                } else {
                    return '<a onclick="((e) => {e.preventDefault(); this.classList.add(`disable`)})()" href="' . $baseUrl . $expressYes . $yes['value'] . '">Sí</a>';
                }
            }
        }

        return '';
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getMagCategoryTree(): array
    {
        $root = $this->_storeManager->getStore()->getRootCategoryId();
        $tree = $this->_categoryManagement->getTree($root)->getChildrenData();
        if (count($tree) > 0) {
            return $tree;
        }
        return [];
    }

    /**
     * @param $category
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function showCategory($category)
    {
        $show = (bool)$category->getIncludeInMenu();
        $isRecipeCategory = $this->isRecipe($category->getId());
        if ($show) {
            if ($isRecipeCategory && !$this->isRecipe) {
                $show = false;
            }
            if (!$isRecipeCategory && $this->isRecipe) {
                $show = false;
            }
        }
        return $show;
    }

    /**
     *
     * Product
     *
     */

    /**
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProductBySku($sku) {
        try {
            return $this->_productRepository->get($sku);
        } catch (\Exception $e) {
            return null;
        }
    }
    //movil inicio
    /**
     * @param $_product
     * @return string
     */
    public function getProductLabelsMobile($_product)
    {
        $labels = '';

        try {
            $store = $this->_storeManager->getStore();

            /**
             * New
             */
            if ($_product->getCustomAttribute('news_from_date') &&
                $_product->getCustomAttribute('news_to_date')) {
                $from = new \DateTime($_product->getCustomAttribute('news_from_date')->getValue());
                $to = new \DateTime($_product->getCustomAttribute('news_to_date')->getValue());
                $today = new \DateTime('today');
                if ($today >= $from && $today <= $to) {
                    $labels = '' . __("Nuevo") . '';
                }
            }

            $flashExpressFest = [];
            /**
             * Flash
             */
            $flashValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_flash', $store->getId());
            $flash = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_flash');
            $flashOptions = $flash->getSource()->getAllOptions();
            foreach ($flashOptions as $option) {
                if ($flashValue === $option['value']) {
                    if (strtolower($option['label']) != 'no') {
                            $flashExpressFest[] = '' . __("Flash"). '';
                    }
                }
            }

            /**
             * Express
             */
            $expressValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_express', $store->getId());
            $express = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_express');
            $expressOptions = $express->getSource()->getAllOptions();
            foreach ($expressOptions as $option) {
                if ($expressValue === $option['value']) {
                    if (strtolower($option['label']) != 'no') {
                        $flashExpressFest[] = '' . __("Express"). '';
                    }
                }
            }

            /**
             * Festivity
             */
            $festValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_fest', $store->getId());
            $fest = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_fest');
            $festOptions = $fest->getSource()->getAllOptions();
            foreach ($festOptions as $option) {
                if ($festValue === $option['value']) {
                    if (strtolower($option['label']) != 'no') {
                        $flashExpressFest[] = '' . __("Pre-Order").  '';
                    }
                }
            }

            if(count($flashExpressFest) > 0) {
                $labels = '' . implode(' | ', $flashExpressFest) . '';
            }

            /**
             * Offer
             */
            $price = $_product->getPrice();
            $specialPrice = $_product->getSpecialPrice();
            $specialFromDate = $_product->getSpecialFromDate();
            $specialToDate = $_product->getSpecialToDate();
            $today = time();
            if (!$specialPrice) {
                $specialPrice = $price;
            }
            if ($specialPrice < $price) {
                if ((is_null($specialFromDate) && is_null($specialToDate)) || ($today >= strtotime($specialFromDate) && is_null($specialToDate)) || ($today <= strtotime($specialToDate) && is_null($specialFromDate)) || ($today >= strtotime($specialFromDate) && $today <= strtotime($specialToDate))) {
                    $labels = '' . __("Oferta") . '';
                }
            }

            /**
             * Sold Out
             */
            if (!$_product->isAvailable()) {
                $labels = '' . __("Agotado") . '';
            }

            return $labels;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    //mobil fin
    /**
     * @param $_product
     * @return string
     */
    public function getProductLabels($_product)
    {
        $labels = '';

        try {
            $store = $this->_storeManager->getStore();

            /**
             * New
             */
            if ($_product->getCustomAttribute('news_from_date') &&
                $_product->getCustomAttribute('news_to_date')) {
                $from = new \DateTime($_product->getCustomAttribute('news_from_date')->getValue());
                $to = new \DateTime($_product->getCustomAttribute('news_to_date')->getValue());
                $today = new \DateTime('today');
                if ($today >= $from && $today <= $to) {
                    $labels = '<label class="new">' . __("Nuevo") . '</label>';
                }
            }

            $flashExpressFest = [];
            /**
             * Flash
             */
            $flashValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_flash', $store->getId());
            $flash = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_flash');
            $flashOptions = $flash->getSource()->getAllOptions();
            foreach ($flashOptions as $option) {
                if ($flashValue === $option['value']) {
                    if (strtolower($option['label']) != 'no') {
                            $flashExpressFest[] = '<span class="flash">' . __("Flash"). '</span>';
                    }
                }
            }

            /**
             * Express
             */
            $expressValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_express', $store->getId());
            $express = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_express');
            $expressOptions = $express->getSource()->getAllOptions();
            foreach ($expressOptions as $option) {
                if ($expressValue === $option['value']) {
                    if (strtolower($option['label']) != 'no') {
                        $flashExpressFest[] = '<span class="express">' . __("Express"). '</span>';
                    }
                }
            }

            /**
             * Festivity
             */
            $festValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_fest', $store->getId());
            $fest = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_fest');
            $festOptions = $fest->getSource()->getAllOptions();
            foreach ($festOptions as $option) {
                if ($festValue === $option['value']) {
                    if (strtolower($option['label']) != 'no') {
                        $flashExpressFest[] = '<span class="festivity">' . __("Pre-Order").  '</span>';
                    }
                }
            }

            if(count($flashExpressFest) > 0) {
                $labels = '<label class="shipping">' . implode(' | ', $flashExpressFest) . '</label>';
            }

            /**
             * Offer
             */
            $price = $_product->getPrice();
            $specialPrice = $_product->getSpecialPrice();
            $specialFromDate = $_product->getSpecialFromDate();
            $specialToDate = $_product->getSpecialToDate();
            $today = time();
            if (!$specialPrice) {
                $specialPrice = $price;
            }
            if ($specialPrice < $price) {
                if ((is_null($specialFromDate) && is_null($specialToDate)) || ($today >= strtotime($specialFromDate) && is_null($specialToDate)) || ($today <= strtotime($specialToDate) && is_null($specialFromDate)) || ($today >= strtotime($specialFromDate) && $today <= strtotime($specialToDate))) {
                    $labels = '<label class="offer">' . __("Oferta") . '</label>';
                }
            }

            /**
             * Sold Out
             */
            if (!$_product->isAvailable()) {
                $labels = '<label class="sold">' . __("Agotado") . '</label>';
            }

            return $labels;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $productImage
     * @param $_product
     * @param false $url
     * @return string
     */
    public function getProductImageHtml($productImage, $_product, $url = false)
    {
        try {
            $store = $this->_storeManager->getStore();

            $mediaSrc = $this->_scopeConfig->getValue('web/secure/base_media_url');
            if (empty($mediaSrc)) {
                $mediaSrc = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            }

            //Fetch from database directly
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT * FROM catalog_product_entity_media_gallery_value WHERE entity_id = " . $_product->getId() . ";";
            $productMedia = $connection->fetchRow($sql);
            if (!empty($productMedia)) {
                $sql = "SELECT * FROM catalog_product_entity_media_gallery WHERE value_id = " . $productMedia['value_id'] . ";";
                $productDbImg = $connection->fetchRow($sql);
                if (!empty($productDbImg)) {
                    if ($url) {
                        return $mediaSrc . 'catalog/product' . $productDbImg['value'] . '?tr=h-400';
                    } else {
                        return '<img class="lazy-load-img" data-src="' . $mediaSrc . 'catalog/product' . $productDbImg['value'] . '?tr=h-400' . '" alt="' . $_product->getName() . '">';
                    }
                }
            }

            //If datatabase empty try from product image
            if ($productImage) {
                if (is_string($productImage->getImageUrl())) {
                    $imgUrl = explode('/', $productImage->getImageUrl());
                    $imgPos = count($imgUrl) - 1;
                    $imgSrc = 'catalog/product/' . $imgUrl[$imgPos - 2] . '/' . $imgUrl[$imgPos - 1] . '/' . $imgUrl[$imgPos] . '?tr=h-400';
                    if ($url) {
                        return $mediaSrc . $imgSrc;
                    } else {
                        return '<img class="lazy-load-img" data-src="' . $mediaSrc . $imgSrc . '" alt="' . $_product->getName() . '">';
                    }
                }
            }

            //Try placeholder
            if (!empty($this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder'))) {
                if ($url) {
                    return $mediaSrc . 'catalog/product/placeholder/' . $this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder');
                } else {
                    return '<img class="lazy-load-img" data-src="' . $mediaSrc . 'catalog/product/placeholder/' . $this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder') . '" alt="' . $_product->getName() . '">';
                }
            }

        } catch (\Exception $e) {
            return '';
        }

        //Fuck off...
        return '';
    }

    // MOBILE START
     /**
     * @param $productImage
     * @param $_product
     * @param false $url
     * @return string
     */
    public function getProductImageMobile($productImage, $_product, $url = false)
    {
        try {
            $store = $this->_storeManager->getStore();

            $mediaSrc = $this->_scopeConfig->getValue('web/secure/base_media_url');
            if (empty($mediaSrc)) {
                $mediaSrc = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            }

            //Fetch from database directly
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT * FROM catalog_product_entity_media_gallery_value WHERE entity_id = " . $_product->getId() . ";";
            $productMedia = $connection->fetchRow($sql);
            if (!empty($productMedia)) {
                $sql = "SELECT * FROM catalog_product_entity_media_gallery WHERE value_id = " . $productMedia['value_id'] . ";";
                $productDbImg = $connection->fetchRow($sql);
                if (!empty($productDbImg)) {
                    if ($url) {
                        return $mediaSrc . 'catalog/product' . $productDbImg['value'] . '?tr=h-400';
                    } else {
                        return '' . $mediaSrc . 'catalog/product' . $productDbImg['value'] . '?tr=h-400' . '' . $_product->getName() . '';
                    }
                }
            }

            //If datatabase empty try from product image
            if ($productImage) {
                if (is_string($productImage->getImageUrl())) {
                    $imgUrl = explode('/', $productImage->getImageUrl());
                    $imgPos = count($imgUrl) - 1;
                    $imgSrc = 'catalog/product/' . $imgUrl[$imgPos - 2] . '/' . $imgUrl[$imgPos - 1] . '/' . $imgUrl[$imgPos] . '?tr=h-400';
                    if ($url) {
                        return $mediaSrc . $imgSrc;
                    } else {
                        return '' . $mediaSrc . $imgSrc . '" alt="' . $_product->getName() . '">';
                    }
                }
            }

            //Try placeholder
            if (!empty($this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder'))) {
                if ($url) {
                    return $mediaSrc . 'catalog/product/placeholder/' . $this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder');
                } else {
                    return '' . $mediaSrc . 'catalog/product/placeholder/' . $this->_scopeConfig->getValue('catalog/placeholder/thumbnail_placeholder') . '' . $_product->getName() . '';
                }
            }

        } catch (\Exception $e) {
            return '';
        }

        //Fuck off...
        return '';
    }
    // MOBILE END
    /**
     * @param $path
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($path)
    {
        $store = $this->_storeManager->getStore();
        $mediaSrc = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaSrc . $path;
    }

    /**
     * @param $images
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function imageKitPdp($images)
    {
        $imagesItems = (array)json_decode($images, true);
        $store = $this->_storeManager->getStore();
        $mediaSrc = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        foreach ($imagesItems as &$imagesItem) {
            $imgUrl = explode('/', $imagesItem['thumb']);
            $imgPos = count($imgUrl) - 1;
            $imgSrc = 'catalog/product/' . $imgUrl[$imgPos - 2] . '/' . $imgUrl[$imgPos - 1] . '/' . $imgUrl[$imgPos] . '?tr=h-200';
            $imagesItem['thumb'] = $mediaSrc . $imgSrc;

            $imgUrl = explode('/', $imagesItem['img']);
            $imgPos = count($imgUrl) - 1;
            $imgSrc = 'catalog/product/' . $imgUrl[$imgPos - 2] . '/' . $imgUrl[$imgPos - 1] . '/' . $imgUrl[$imgPos] . '?tr=h-600';
            $imagesItem['img'] = $mediaSrc . $imgSrc;

            $imgUrl = explode('/', $imagesItem['full']);
            $imgPos = count($imgUrl) - 1;
            $imgSrc = 'catalog/product/' . $imgUrl[$imgPos - 2] . '/' . $imgUrl[$imgPos - 1] . '/' . $imgUrl[$imgPos];
            $imagesItem['full'] = $mediaSrc . $imgSrc;
        }

        return json_encode($imagesItems);
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getNutritionalImage($product)
    {
        $images = $product->getMediaGalleryImages();
        foreach ($images->getItems() as $image) {
            $fileName = strtoupper($image->getData('file'));
            if (strpos($fileName, 'NUT') !== false) {
                return $image->getData('url');
            }
        }
        return null;
    }

    /**
     * @param $_product
     * @return mixed
     */
    public function getIsRecipe($_product)
    {
        try {
            $store = $this->_storeManager->getStore();

            return (bool)$this->_productResource->getAttributeRawValue($_product->getId(), 'is_recipe', $store->getId());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $value
     */
    public function setIsRecipe($value)
    {
        $this->isRecipe = $value;
    }

    /**
     * @param $_product
     * @return mixed
     */
    public function getMinSale($_product)
    {
        try {
            $store = $this->_storeManager->getStore();
            $min = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_un_min_venta', $store->getId());
            if (!is_string($min)) {
                return 1;
            } else {
                return $min;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    // moviles inicio
    /**
     * @param $_product
     * @param false $text
     * @param array $options
     * @return string
     */
    public function getPresentacionMovil($_product, $text = false, $options = [], $id = false, $showShippingTag = false)
    {
        try {
            if ($_product) {
                $store = $this->_storeManager->getStore();
                $productId = $id ? $_product : $_product->getId();

                $isPapa = $this->_configurable->getParentIdsByChild($productId);
                if (isset($isPapa[0])) {
                    $productId = $isPapa[0];
                }

                if (is_string($presentacion = $this->_productResource->getAttributeRawValue($productId, 'ls_presentacion', $store->getId()))) {
                    $optionsTxt = [];
                    foreach ($options as $option) {
                        $optionsTxt[] = ' ' . $option['value'];
                    }
                    $optionsTxt = implode(',', $optionsTxt);

                    $tag = [];
                    if ($showShippingTag) {
                        //Is flash
                        $isFlash = true;
                        $flashValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_flash', $store->getId());
                        if (is_array($flashValue) || !$flashValue) {
                            $isFlash = false;
                        }
                        $flash = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_flash');
                        $flashOptions = $flash->getSource()->getAllOptions();
                        foreach ($flashOptions as $option) {
                            if ($flashValue === $option['value']) {
                                if (strtolower($option['label']) == 'no') {
                                    $isFlash = false;
                                }
                            }
                        }

                        //Is express
                        $isExpress = true;
                        $expressValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_express', $store->getId());
                        if (is_array($expressValue) || !$expressValue) {
                            $isExpress = false;
                        }
                        $express = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_express');
                        $expressOptions = $express->getSource()->getAllOptions();
                        foreach ($expressOptions as $option) {
                            if ($expressValue === $option['value']) {
                                if (strtolower($option['label']) == 'no') {
                                    $isExpress = false;
                                }
                            }
                        }

                        //Is festivity
                        $isFest = true;
                        $festValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_fest', $store->getId());
                        if(is_array($festValue) || !$festValue) {
                            $isFest = false;
                        }
                        $fest = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_fest');
                        $festOptions = $fest->getSource()->getAllOptions();
                        foreach ($festOptions as $option) {
                            if ($festValue === $option['value']) {
                                if (strtolower($option['label']) == 'no') {
                                    $isFest = false;
                                }
                            }
                        }

                        if($text) {
                            if($isExpress) {
                                $tag[] = 'express';
                            }
                            if($isFlash) {
                                $tag[] = 'flash';
                            }
                            if($isFest) {
                                $tag = 'pre-order';
                            }
                            $tag = '&nbsp;' . implode(' | ', $tag);
                        } else {
                            if($isExpress) {
                                $tag[] = 'express';
                            }
                            if($isFlash) {
                                $tag[] = 'flash';
                            }
                            if($isFest) {
                                $tag[] = 'pre-order';
                            }
                            $tag = '' . implode(' | ', $tag) . '';
                        }
                    } else {
                        $tag = '';
                    }

                    if ($text) {
                        return $presentacion . $optionsTxt . $tag;
                    } else {
                        return '' . $presentacion . $optionsTxt . $tag . '';
                    }
                }
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
    //moviles fin
    /**
     * @param $_product
     * @param false $text
     * @param array $options
     * @return string
     */
    public function getPresentacion($_product, $text = false, $options = [], $id = false, $showShippingTag = false)
    {
        try {
            if ($_product) {
                $store = $this->_storeManager->getStore();
                $productId = $id ? $_product : $_product->getId();

                $isPapa = $this->_configurable->getParentIdsByChild($productId);
                if (isset($isPapa[0])) {
                    $productId = $isPapa[0];
                }

                if (is_string($presentacion = $this->_productResource->getAttributeRawValue($productId, 'ls_presentacion', $store->getId()))) {
                    $optionsTxt = [];
                    foreach ($options as $option) {
                        $optionsTxt[] = ' ' . $option['value'];
                    }
                    $optionsTxt = implode(',', $optionsTxt);

                    $tag = [];
                    if ($showShippingTag) {
                        //Is flash
                        $isFlash = true;
                        $flashValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_flash', $store->getId());
                        if (is_array($flashValue) || !$flashValue) {
                            $isFlash = false;
                        }
                        $flash = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_flash');
                        $flashOptions = $flash->getSource()->getAllOptions();
                        foreach ($flashOptions as $option) {
                            if ($flashValue === $option['value']) {
                                if (strtolower($option['label']) == 'no') {
                                    $isFlash = false;
                                }
                            }
                        }

                        //Is express
                        $isExpress = true;
                        $expressValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_express', $store->getId());
                        if (is_array($expressValue) || !$expressValue) {
                            $isExpress = false;
                        }
                        $express = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_express');
                        $expressOptions = $express->getSource()->getAllOptions();
                        foreach ($expressOptions as $option) {
                            if ($expressValue === $option['value']) {
                                if (strtolower($option['label']) == 'no') {
                                    $isExpress = false;
                                }
                            }
                        }

                        //Is festivity
                        $isFest = true;
                        $festValue = $this->_productResource->getAttributeRawValue($_product->getId(), 'ls_envio_fest', $store->getId());
                        if(is_array($festValue) || !$festValue) {
                            $isFest = false;
                        }
                        $fest = $this->_eavConfig->getAttribute('catalog_product', 'ls_envio_fest');
                        $festOptions = $fest->getSource()->getAllOptions();
                        foreach ($festOptions as $option) {
                            if ($festValue === $option['value']) {
                                if (strtolower($option['label']) == 'no') {
                                    $isFest = false;
                                }
                            }
                        }

                        if($text) {
                            if($isExpress) {
                                $tag[] = 'express';
                            }
                            if($isFlash) {
                                $tag[] = 'flash';
                            }
                            if($isFest) {
                                $tag = 'pre-order';
                            }
                            $tag = '&nbsp;' . implode(' | ', $tag);
                        } else {
                            if($isExpress) {
                                $tag[] = '<span class="ex">express</span>';
                            }
                            if($isFlash) {
                                $tag[] = '<span class="flash">flash</span>';
                            }
                            if($isFest) {
                                $tag[] = '<span class="fest">pre-order</span>';
                            }
                            $tag = '<br/><span class="tag">' . implode(' | ', $tag) . '</span>';
                        }
                    } else {
                        $tag = '';
                    }

                    if ($text) {
                        return $presentacion . $optionsTxt . $tag;
                    } else {
                        return '<div class="presentacion">' . $presentacion . $optionsTxt . $tag . '</div>';
                    }
                }
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
    // MOVIL INICIO
    /**
     * @param $_product
     * @return string
     */
    public function getUMMovil($_product)
    {
        try {
            $store = $this->_storeManager->getStore();
            $productId = $_product->getId();

            $isPapa = $this->_configurable->getParentIdsByChild($_product->getId());
            if (isset($isPapa[0])) {
                $productId = $isPapa[0];
            }
            if (is_string($um = $this->_productResource->getAttributeRawValue($productId, 'ls_unidad_medida', $store->getId()))) {
                return $um;
            }
            return "";
        } catch (\Exception $e) {
            return '';
        }
    }
    // MOVIL FIN
    /**
     * @param $_product
     * @return string
     */
    public function getUM($_product)
    {
        try {
            $store = $this->_storeManager->getStore();
            $productId = $_product->getId();

            $isPapa = $this->_configurable->getParentIdsByChild($_product->getId());
            if (isset($isPapa[0])) {
                $productId = $isPapa[0];
            }
            if (is_string($um = $this->_productResource->getAttributeRawValue($productId, 'ls_unidad_medida', $store->getId()))) {
                return "<span class='kip-um'>/ {$um}</span>";
            }
            return "";
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $_product
     * @return string
     */
    public function getInfoPesoVar($_product)
    {
        try {
            $store = $this->_storeManager->getStore();
            $productId = $_product->getId();

            $isPapa = $this->_configurable->getParentIdsByChild($_product->getId());
            if (isset($isPapa[0])) {
                $productId = $isPapa[0];
            }
            if (is_string($ipv = $this->_productResource->getAttributeRawValue($productId, 'ls_info_peso_var', $store->getId()))) {
                return "<p class='kip-ipv'>{$ipv}</p>";
            }

            return '';
            //return "<p class='kip-ipv'>Peso variable: se agrega rango máximo al carrito. Se reservan fondos en tarjeta y se cobra peso exacto al facturar.</p>";
        } catch (\Exception $e) {
            return '';
        }
    }
    public function getMainProduct($_product){
        $productId = 5876; //this is child product id
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($productId);
         if(isset($product[0])){
             //this is parent product id..
             return $product[0];
        }           
        else{
            return "simple";
        } 
    }
   

        public function getChildProducts($_product)
        {
            
            // $this->_logger->addInfo(print_r($_product, true));  

            // $productId = 5880; //Configurable product ID
            $productId = $_product; //Configurable product ID
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $_product = $_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $_childProducts = $_product->getTypeInstance()->getUsedProducts($_product);
            $ret = "{VACIO:'VACIO'}";
            foreach ($_childProducts as $simpleProduct){                    
                    $this->_logger->addInfo(print_r($simpleProduct->getId(), true));                     
                    $this->_logger->addInfo(print_r('asking------------------------------------------------>'.$simpleProduct->getId(), true));                      
                    $this->_logger->addInfo(print_r(json_encode($simpleProduct), true));     
                    $ret=$simpleProduct;                                                     
                    $product = $this->_productRepository->getById(5881);                                                            
                    $ret = $product;
                    $this->_logger->addInfo(print_r(json_encode($ret), true));     
                    $vx = $this->getAtcPapa($simpleProduct,1);
                    $ret = $vx;
            }
            return json_encode($ret);                        
        }
   /**
     * @param $_product
     * @param $qty
     * @return array|false
     */
    public function getAtcPapa($_product, $qty)
    {
        try {
            $store = $this->_storeManager->getStore();
            $isPapa = $this->_configurable->getParentIdsByChild($_product->getId());
            if (isset($isPapa[0])) {
                $papaId = $isPapa[0];

                /** @var \Magento\Catalog\Model\Product $papa */
                $papa = $this->_productRepository->getById($papaId);
                if ($papa->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    return false;
                }

                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
                $productTypeInstance = $papa->getTypeInstance();
                $productTypeInstance->setStoreFilter($papa->getStoreId(), $papa);
                $attributes = $productTypeInstance->getConfigurableAttributes($papa);
                $atc = [
                    "item" => $papa->getId(),
                    "options" => [],
                    "product" => $papa->getId(),
                    "qty" => $qty,
                    "selected_configurable_option" => $_product->getId(),
                    "super_attribute" => [],
                ];

                foreach ($attributes as $_attribute) {
                    $attributeCode = $_attribute->getProductAttribute()->getAttributeCode();
                    if (is_string($attr = $this->_productResource->getAttributeRawValue($_product->getId(), $attributeCode, $store->getId()))) {
                        $atc["super_attribute"][$_attribute->getAttributeId()] = $attr;
                    }
                }

                return json_encode($atc);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    

    /**
     * @param $_product
     * @param false $id
     * @param bool $icons
     * @return false|string
     */
    public function getRecipeProps($_product, $id = false, $icons = true)
    {
        try {
            $store = $this->_storeManager->getStore();
            $time = $this->_productResource->getAttributeRawValue($id ? $_product : $_product->getId(), 'recipe_time', $store->getId());
            $ing = $this->_productResource->getAttributeRawValue($id ? $_product : $_product->getId(), 'recipe_ingredients_total', $store->getId());
            $port = $this->_productResource->getAttributeRawValue($id ? $_product : $_product->getId(), 'recipe_portions', $store->getId());
            $r = '';

            if ($icons) {
                if (is_string($port)) {
                    $r .= '<p class="port"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M7.125 12C8.98896 12 10.5 10.489 10.5 8.625C10.5 6.76104 8.98896 5.25 7.125 5.25C5.26104 5.25 3.75 6.76104 3.75 8.625C3.75 10.489 5.26104 12 7.125 12Z" fill="#48D597"/>
<path d="M10.9688 13.875C9.64875 13.2047 8.19187 12.9375 7.125 12.9375C5.03531 12.9375 0.75 14.2191 0.75 16.7812V18.75H7.78125V17.9967C7.78125 17.1061 8.15625 16.2131 8.8125 15.4688C9.33609 14.8744 10.0692 14.3227 10.9688 13.875Z" fill="#48D597"/>
<path d="M15.9375 13.5C13.4967 13.5 8.625 15.0075 8.625 18V20.25H23.25V18C23.25 15.0075 18.3783 13.5 15.9375 13.5Z" fill="#48D597"/>
<path d="M15.9375 12C18.2157 12 20.0625 10.1532 20.0625 7.875C20.0625 5.59683 18.2157 3.75 15.9375 3.75C13.6593 3.75 11.8125 5.59683 11.8125 7.875C11.8125 10.1532 13.6593 12 15.9375 12Z" fill="#48D597"/>
</svg>' . $port . ' personas</p>';
                }
                if (is_string($time)) {
                    $r .= '<p class="time"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12 2.25C6.615 2.25 2.25 6.615 2.25 12C2.25 17.385 6.615 21.75 12 21.75C17.385 21.75 21.75 17.385 21.75 12C21.75 6.615 17.385 2.25 12 2.25ZM8.14078 7.60969L13.0627 10.9378C13.3876 11.1718 13.6073 11.5243 13.6743 11.919C13.7413 12.3138 13.6502 12.719 13.4207 13.0471C13.1913 13.3752 12.8418 13.5998 12.4481 13.6723C12.0543 13.7447 11.6478 13.6593 11.3166 13.4344C11.171 13.3317 11.0442 13.2049 10.9416 13.0594L7.61344 8.1375C7.56221 8.06411 7.53844 7.97503 7.54631 7.88588C7.55417 7.79672 7.59316 7.71318 7.65645 7.64989C7.71974 7.5866 7.80328 7.54761 7.89244 7.53974C7.98159 7.53188 8.07067 7.55564 8.14406 7.60687L8.14078 7.60969ZM12 20.25C7.45312 20.25 3.75 16.5492 3.75 12C3.74614 10.8904 3.968 9.79163 4.4021 8.77048C4.83621 7.74933 5.47348 6.82714 6.27516 6.06C6.3375 5.99784 6.4116 5.94872 6.49312 5.9155C6.57465 5.88227 6.66197 5.86561 6.75 5.86648C6.83804 5.86735 6.92501 5.88574 7.00587 5.92057C7.08672 5.9554 7.15983 6.00598 7.22093 6.06935C7.28204 6.13273 7.32992 6.20764 7.36177 6.28971C7.39363 6.37178 7.40883 6.45937 7.40649 6.54737C7.40415 6.63538 7.38431 6.72203 7.34814 6.80229C7.31196 6.88255 7.26017 6.95481 7.19578 7.01484C6.52306 7.65874 5.98829 8.4327 5.62398 9.28969C5.25967 10.1467 5.07342 11.0688 5.07656 12C5.07656 15.8175 8.1825 18.9234 12 18.9234C15.8175 18.9234 18.9234 15.8175 18.9234 12C18.9234 8.40609 16.1709 5.44313 12.6633 5.10938V7.6875C12.6633 7.86341 12.5934 8.03212 12.469 8.15651C12.3446 8.2809 12.1759 8.35078 12 8.35078C11.8241 8.35078 11.6554 8.2809 11.531 8.15651C11.4066 8.03212 11.3367 7.86341 11.3367 7.6875V4.41328C11.3367 4.23737 11.4066 4.06866 11.531 3.94427C11.6554 3.81988 11.8241 3.75 12 3.75C16.5492 3.75 20.25 7.45312 20.25 12C20.25 16.5469 16.5492 20.25 12 20.25Z" fill="#48D597"/>
</svg>' . $time . ' min.</p>';
                }
                if (is_string($ing)) {
                    $r .= '<p class="ing"><svg width="80" height="101" viewBox="0 0 80 101" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M68.6135 34.6596C65.3388 31.3154 61.5552 28.7686 57.3291 26.9304C58.7673 26.7311 60.0949 26.3767 61.157 25.7788C65.5158 23.3205 68.3922 18.4039 68.4365 13.3988C68.4586 11.2284 67.817 8.79224 65.9584 7.68491C64.5201 6.82119 62.7058 6.95407 61.0685 7.30841C54.6297 8.65936 48.8326 13.7753 45.6686 19.8434C45.8677 19.024 46.089 18.2267 46.1554 17.3852C46.3988 14.8826 45.8898 12.2028 44.8278 9.85528C43.7215 7.52988 42.1948 5.51453 40.4025 3.89782C38.6103 2.25896 36.6189 0.952308 34.3178 0C34.3178 0 43.4117 12.7565 43.7878 23.7413C42.4824 23.6527 41.1327 23.5862 39.7609 23.5862C27.7906 23.5862 18.1657 27.2847 10.9082 34.7039C3.6287 42.0787 0 51.2032 0 62.0551C0 72.907 3.6287 82.0314 10.9082 89.4505C18.1878 96.8697 27.7906 100.568 39.7609 100.568C51.7312 100.568 61.3561 96.8697 68.6135 89.4505C75.893 82.0314 79.5217 72.907 79.5217 62.0551C79.5217 51.2032 75.8709 42.0787 68.6135 34.6596Z" fill="#48D597"/>
</svg><strong>' . $ing . '</strong> ingr.</p>';
                }
            } else {
                return json_encode([
                    "time" => $time . ' min.',
                    "ingredients" => $ing . ' ingr.',
                    "portions" => $port . ' personas',
                ]);
            }

            return $r;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $_product
     * @return array|bool|string
     */
    public function getIngredients($_product)
    {
        try {
            $store = $this->_storeManager->getStore();
            return $this->_productResource->getAttributeRawValue($_product->getId(), 'recipe_ingredients', $store->getId());
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $product \Magento\Catalog\Model\Product
     */
    public function getProductCategoryBreadcrumb($product)
    {
        $categories = $product->getCategoryIds();
        if (count($categories) > 0) {
            try {
                if (isset($categories[2])) {
                    $model = $this->_categoryRepository->get($categories[2]);
                } else {
                    $model = $this->_categoryRepository->get($categories[count($categories) - 1]);
                }
                return '<li><a href="' . $model->getUrl() . ' ">
                           ' . $model->getName() . '
                        </a></li>';
            } catch (\Exception $e) {

            }
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getExpressLimit()
    {
        return $this->_scopeConfig->getValue('carriers/kipping_express/products_limit');
    }

    /**
     * @return mixed
     */
    public function getFlashLimit()
    {
        return $this->_scopeConfig->getValue('carriers/kipping_flash/products_limit');
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    public function getCheckoutImpulseProducts()
    {
        try {
            $checkoutSi = null;
            $checkout = $this->_eavConfig->getAttribute('catalog_product', 'ls_checkout');
            $checkoutOptions = $checkout->getSource()->getAllOptions();
            foreach ($checkoutOptions as $option) {
                if (strtolower($option['label']) == 'si') {
                    $checkoutSi = $option['value'];
                }
            }
            $collection = $this->_productCollectionFactory->create();
            $collection
                ->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('ls_checkout', $checkoutSi)
                ->setPageSize(15)
                ->getSelect()
                ->orderRand();            
            return $collection; 
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    public function getCheckoutImpulseProductsv2($notProducts)
    {
        // print_r($notProducts);   
        $this->_logger->addInfo(print_r("STARTINGBUILDV2:", true));
        $orders = $this->_orderCollection
            ->addFieldToFilter('customer_id', $this->getCustomerId())      
            ->setOrder(
                'created_at',
                'desc'
            )      
            ->setPageSize(15) // only get 10 orders 
            ->setCurPage(1)  // first page (means limit 0,10)
            ->load();


        $products = [];
        $productSkus = [];
        $collection = [];

        
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            /**
             * @var \Magento\Sales\Model\Order\Item $item
             */

            foreach ($order->getAllVisibleItems() as $item) {
                if($item->getProduct()) {
                    if($item->getProduct()->getVisibility() == Visibility::VISIBILITY_BOTH) {                     
                        array_push($productSkus, [$item->getSku()][0]);                     
                    }                
                }
            } 
            
            try{
                $collection = $this->_productCollectionFactory->
                create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('sku', array('in' => $productSkus))
                ->setPageSize(30)  
                ->setCurPage(1)
                ->load();
                ;            
            } catch (\Exception $e) {
            }
            
            
                      
        }
        
        $this->_logger->addInfo(print_r($productSkus, true));

        if (count($collection) > 0) {
            $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            // ->addAttributeToFilter('sku', array('in' => $productSkus))
            // ->addAttributeToFilter('sku', array('nin' => $notProducts))            
            //->setPageSize(10) 
            //->setCurPage(1) 
            ->load();
            //->getSelect();
            
        }
        else{
            $collection = [];
        }
        
            // echo(count($collection));
            return $collection;
}

    /**
     *
     * Wishlists
     *
     */

    /**
     * Get current customer wish list items count
     *
     * @return mixed
     */
    public function wishListCount()
    {
        try {
            return count($this->wishLists());
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @param bool $values
     * @param bool $recipes
     * @return array|string
     */
    public function wishLists($values = true, $recipes = false)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create("Magento\Customer\Model\Session");
            if ($wishlist = $this->_wishlist->loadByCustomerId($this->_customer->getCustomerId(), true)) {
                $items = $this->getWishlistItems($wishlist->getId());
                $lists = [];
                foreach ($items as $item) {
                    $product = $this->_productRepository->getById($item['product_id']);
                    foreach (explode(',', $item['description']) as $l) {
                        if (!isset($lists[$this->seoUrl($l)])) {
                            $image_url = $this->_imageHelper
                                ->init($product, 'product_page_image_small')
                                ->setImageFile($product->getImage())
                                ->resize(200, 200)
                                ->getUrl();

                            $lists[$this->seoUrl($l)] = [
                                "value" => $l,
                                "thumb" => $image_url,
                                "count" => 1,
                                "url" => '/kip/wishlist/products?' . 'wishl' . $this->seoUrl($l) . '=' . time(),
                                "products" => [$item['product_id']],
                            ];
                        } else {
                            $lists[$this->seoUrl($l)]["count"] += 1;
                            $lists[$this->seoUrl($l)]["products"][] += $item['product_id'];
                        }
                    }
                }

                if (!$recipes) {
                    unset($lists['recipesgroupnotuse']);
                }

                return $values ? array_values($lists) : $lists;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed
     */
    public function currentWishList()
    {
        try {
            $params = $this->getRequest()->getParams();
            foreach ($params as $param => $value) {
                if (strpos($param, 'wishl') !== false) {
                    return str_replace('wishl', '', $param);
                }
            }
        } catch (\Exception $e) {
            return '';
        }
        return '';
    }

    /**
     * @param $id
     * @param null $product_id
     * @return array|mixed
     */
    public function getWishlistItems($id, $product_id = null)
    {
        if ($id) {
            $connection = $this->_resourceConnection->getConnection();

            $sql = "select * from wishlist_item where wishlist_id = " . $id;

            if ($product_id) {
                $sql .= " and product_id = " . $product_id;
                return $connection->fetchRow($sql);
            } else {
                return $connection->fetchAll($sql);
            }
        } else {
            return [];
        }
    }

    /**
     * @param $item_id
     */
    public function deleteWishlistItem($item_id)
    {
        if ($item_id) {
            $connection = $this->_resourceConnection->getConnection();

            $connection->delete(
                $this->_resourceConnection->getTableName('wishlist_item'),
                [
                    'wishlist_item_id = ?' => $item_id,
                ]
            );
        }
    }

    /**
     *
     * Others
     *
     */

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface|null
     */
    public function getCartTotals()
    {
        try {
            return $this->_cartTotalRepository->get($this->_checkoutSession->getQuote()->getId());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        $request = $this->_context->getRequest();
        $moduleName = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        return $moduleName . $controller . $action;
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    public function seoUrl($string)
    {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "_", $string);
        return $string;
    }

    /**
     * @param $customerId
     * @return array
     */
    public function recurringProducts($customerId = null, $skus = false)
    {
        
        $orders = $this->_orderCollection
            ->addFieldToFilter('customer_id', $customerId ?? $this->getCustomerId())            
            ->getItems();

        $products = [];

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        foreach ($orders as $order) {
            /**
             * @var \Magento\Sales\Model\Order\Item $item
             */
            foreach ($order->getAllVisibleItems() as $item) {
                if($item->getProduct()) {
                    if($item->getProduct()->getVisibility() == Visibility::VISIBILITY_BOTH) {
                        if (!isset($products[$item->getProductId()])) {
                            $products[$item->getProductId()]['id'] = $skus ? $item->getSku() : $item->getProductId();
                            $products[$item->getProductId()]['times'] = 1;
                        } else {
                            $products[$item->getProductId()]['times'] += 1;
                        }
                    }
                }
            }
        }
        if(count($products) > 0){
            // krsort($products);
            $price = array();            
            $price = array_column($products, 'times');
            array_multisort($price, SORT_DESC, $products);                                    
        }                    
        // print_r($products);        
        return array_slice($products, 0, 125);
        // return $products;
    }

    /**
     * @param $document_id
     * @return array|\Ls\Omni\Client\Ecommerce\Entity\SalesEntry|\Ls\Omni\Client\Ecommerce\Entity\SalesEntryGetResponse|\Ls\Omni\Client\ResponseInterface|mixed|null
     */
    public function getLsReceiptFromLsDocumentId($document_id)
    {
        $omniReceipt = null;
        try {
            if ($document_id) {
                $sessionTag = $document_id . 'TMP_LS_R_LOAD';
                if (isset($_SERVER[$sessionTag])) {
                    $omniReceipt = json_decode($_SERVER[$sessionTag], true);
                } else {
                    if ($omniReceipt = $this->_omniOrderHelper->getOrderDetailsAgainstId(
                        $document_id,
                        DocumentIdType::RECEIPT
                    )) {
                        $omniReceipt = $this->getSaleEntry($omniReceipt);
                        $_SERVER[$sessionTag] = json_encode($omniReceipt);
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $omniReceipt;
    }

    /**
     * @param $document_id
     * @return array|\Ls\Omni\Client\Ecommerce\Entity\SalesEntry|\Ls\Omni\Client\Ecommerce\Entity\SalesEntryGetResponse|\Ls\Omni\Client\ResponseInterface|mixed|null
     */
    public function getLsOrderFromLsDocumentId($document_id)
    {
        $omniOrder = null;
        try {
            if ($document_id) {
                $sessionTag = $document_id . 'TMP_LS_O_LOAD';
                if (isset($_SERVER[$sessionTag])) {
                    $omniOrder = json_decode($_SERVER[$sessionTag], true);
                } else {
                    if ($omniOrder = $this->_omniOrderHelper->getOrderDetailsAgainstId(
                        $document_id
                    )) {
                        $omniOrder = $this->getSaleEntry($omniOrder);
                        $_SERVER[$sessionTag] = json_encode($omniOrder);
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $omniOrder;
    }

    /**
     * @param $document_id
     * @return \Magento\Framework\DataObject|null
     */
    public function getMagOrderFromLsDocumentId($document_id)
    {
        try {
            if ($document_id) {
                return $this->_orderCollection
                    ->addFieldToFilter('document_id', $document_id)
                    ->getFirstItem();
            }
        } catch (\Exception $e) {
        }
        return null;
    }

    /**
     * @param $lsOrderId
     * @param $sku
     * @return int[]
     */
    public function getLsInvoicedItem($lsOrderId, $sku)
    {
        $invoicedItem = [
            'qty' => -1,
            'amount' => -1
        ];

        if ($_omniOrder = $this->getLsOrderFromLsDocumentId($lsOrderId)) {
            $omniReceipt = $this->getLsReceiptFromLsDocumentId($_omniOrder['receipt_id']);
        } else {
            $omniReceipt = $this->getLsReceiptFromLsDocumentId($lsOrderId);
        }
        if ($omniReceipt) {
            foreach ($omniReceipt['items'] as $receiptItem) {
                $sku = explode('-', $sku);
                if (isset($sku[0])) {
                    $sku = $sku[0];
                } else {
                    $sku = implode('-', $sku);
                }
                if ($receiptItem['item_id'] === $sku) {
                    if ($invoicedItem['qty'] === -1) {
                        $invoicedItem['qty'] = 0;
                        $invoicedItem['amount'] = 0;
                    }
                    $invoicedItem['qty'] += $receiptItem['quantity'];
                    $invoicedItem['amount'] += $receiptItem['amount'];
                }
            }
        }

        return $invoicedItem;
    }

    /**
     * Get Sales entry info
     * @param SalesEntry $salesEntry
     * @return array
     */
    private function getSaleEntry(SalesEntry $salesEntry): array
    {
        return [
            'id' => $salesEntry->getId(),
            'click_and_collect_order' => $salesEntry->getClickAndCollectOrder(),
            'document_reg_time' => $salesEntry->getDocumentRegTime(),
            'document_id' => $salesEntry->getCustomerOrderNo(),
            'receipt_id' => $salesEntry->getCustReceiptNo(),
            'external_id' => $salesEntry->getExternalId(),
            //'payment_status' => $salesEntry->getPaymentStatus(),
            'id_type' => $salesEntry->getIdType(),
            'line_item_count' => $this->formatValue($salesEntry->getLineItemCount(), false),
            'points_rewarded' => $this->formatValue($salesEntry->getPointsRewarded(), false),
            'points_used' => $this->formatValue($salesEntry->getPointsUsedInOrder(), false),
            'posted' => $salesEntry->getPosted(),
            'ship_to_name' => $salesEntry->getShipToName(),
            'ship_to_email' => $salesEntry->getShipToEmail(),
            'status' => $salesEntry->getStatus(),
            'store_id' => $salesEntry->getStoreId(),
            'store_name' => $salesEntry->getStoreName(),
            //'total_net_amount' => $this->formatValue($salesEntry->getTotalAmount() + $salesEntry->getTotalDiscount()),
            'total_net_amount' => $this->formatValue($salesEntry->getTotalAmount()/*+ $salesEntry->getTotalDiscount()*/),
            'total_shipping' => $this->formatValue($this->getShipmentChargeLineFee($salesEntry)),
            'total_discount' => $this->formatValue($salesEntry->getTotalDiscount()),
            'total_amount' => $this->formatValue($salesEntry->getTotalAmount()),
            'contact_address' => $this->_omniGraphHelper->getAddress($salesEntry->getContactAddress()),
            'ship_to_address' => $this->_omniGraphHelper->getAddress($salesEntry->getShipToAddress()),
            'payments' => $this->getPayments($salesEntry->getPayments()),
            'items' => $this->getItems($salesEntry->getLines())
        ];
    }

    /**
     * @return float|int
     */
    public function getShipmentChargeLineFee($salesEntry)
    {
        $orderLines = $salesEntry->getLines();
        $fee = 0;
        foreach ($orderLines as $key => $line) {
            if ($line->getItemId() == $this->_scopeConfig->getValue(LSR::LSR_SHIPMENT_ITEM_ID)) {
                $fee = $line->getAmount();
                break;
            }
        }
        return $fee;
    }

    /**
     * Get items array
     * @param ArrayOfSalesEntryLine $items
     * @return array
     */
    private function getItems(ArrayOfSalesEntryLine $items): array
    {
        $itemsArray = [];
        $this->_logger->addInfo(print_r("-ITEMSENDTOLS->====================================================================================", true));
        foreach ($items->getSalesEntryLine() as $item) {
            $this->_logger->addInfo(print_r("-ITEMSENDTOLS->", true));            
            $this->_logger->addInfo(print_r($item, true));  
            $this->_logger->addInfo(print_r("-ITEMSENDTOLS-<", true));
            $itemsArray[] = [
                'amount' => $item->getAmount(),
                'click_and_collect_line' => $item->getClickAndCollectLine(),
                'discount_amount' => $this->formatValue($item->getDiscountAmount()),
                'discount_percent' => $this->formatValue($item->getDiscountPercent(), false),
                'item_description' => $item->getItemDescription(),
                'item_id' => $item->getItemId(),
                'item_image_id' => $item->getItemImageId(),
                'line_number' => $item->getLineNumber(),
                'line_type' => $item->getLineType(),
                'net_amount' => $this->formatValue($item->getNetAmount()),
                'net_price' => $this->formatValue($item->getNetPrice()),
                'parent_line' => $item->getParentLine(),
                'price' => $this->formatValue($item->getPrice()),
                'quantity' => $item->getQuantity(),
                'store_id' => $item->getStoreId(),
                'tax_amount' => $this->formatValue($item->getTaxAmount()),
                'uom_id' => $item->getUomId(),
                'variant_description' => $item->getVariantDescription(),
                'variant_id' => $item->getVariantId()
            ];
        }

        return $itemsArray;
    }

    /**
     * Get payments array
     * @param ArrayOfSalesEntryPayment $payments
     * @return array
     */
    public function getPayments(ArrayOfSalesEntryPayment $payments): array
    {
        $paymentsArray = [];
        foreach ($payments->getSalesEntryPayment() as $payment) {
            $paymentsArray[] = [
                'amount' => $this->formatValue($payment->getAmount()),
                'card_no' => $payment->getCardNo(),
                'currency_code' => $payment->getCurrencyCode(),
                'currency_factor' => $this->formatValue($payment->getCurrencyFactor()),
                'line_number' => $payment->getLineNumber(),
                'tender_type' => $payment->getTenderType(),
            ];
        }

        return $paymentsArray;
    }

    /**
     * @param $value
     * @param bool $dollar
     * @return string
     */
    public function formatValue($value, $dollar = true)
    {
        return ($dollar ? '$' : '') . number_format($value, 2);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path);
    }
}
