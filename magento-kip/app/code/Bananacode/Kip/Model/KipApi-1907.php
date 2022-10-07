<?php

namespace Bananacode\Kip\Model;

use Ls\Omni\Client\Ecommerce\Entity\OneList;
use Ls\Omni\Client\Ecommerce\Operation;
use Ls\Omni\Client\Ecommerce\Entity;
use Ls\Core\Model\LSR;
use Ls\Omni\Helper\BasketHelper;
use Ls\Omni\Helper\Data;
use Ls\Omni\Helper\ItemHelper;
use Ls\Omni\Helper\LoyaltyHelper;
use Bananacode\Kip\Api\KipApiInterface;
use Bananacode\Referral\Model\Referral;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

class KipApi implements KipApiInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    private $_cart;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    private $_kip;

    /**
     * @var LoyaltyHelper
     */
    private $loyaltyHelper;

    /**
     * @var BasketHelper
     */
    private $basketHelper;

    /**
     * @var LSR
     */
    public $lsr;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    public $_userContext;

    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $_cartRepository;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $_tokenFactory;

    /**
     * @var ItemHelper
     */
    public $lsItemHelper;

    /**
     * @var Data
     */
    public $lsData;

    /**
     * @var \Monolog\Logger
     */
    private $_logger;

    /**
     * @var \Magento\InventorySales\Model\GetProductSalableQty
     */
    private $_getProductSalableQty;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    private $_stockResolver;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    private $_productConfig;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $_pageFactory;

    /**
     * @var \Bananacode\Kip\Block\CustomBlocks
     */
    private $_customBlocks;

    /**
     * @var \Magento\Widget\Model\Template\FilterEmulate
     */
    private $_template;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $_formKey;

    /**
     * @var \Magento\Framework\App\State
     */
    private $_appState;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $_emulation;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $_stockItemRepository;

    /**
     * @var \Bananacode\Referral\Model\ResourceModel\Referral
     */
    private $_referralResourceModel;

    /**
     * @var Referral
     */
    private $_referralModel;

    /**
     * @var \Bananacode\Kip\Helper\Notify
     */
    private $_bananaNotify;

    /**
     * @var \Magento\Quote\Model\MaskedQuoteIdToQuoteId
     */
    private $_maskedQuoteIdToQuoteId;

    /**
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $cart
     * @param \Bananacode\Kip\Block\Main $kip
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
     * @param \Magento\InventorySales\Model\GetProductSalableQty $getProductSalableQty
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Bananacode\Kip\Block\CustomBlocks $customBlocks
     * @param \Magento\Widget\Model\Template\FilterEmulate $template
     * @param LSR $lsr
     * @param Data $lsData
     * @param ItemHelper $lsItemHelper
     * @param LoyaltyHelper $loyaltyHelper
     * @param BasketHelper $basketHelper
     * @param MailInterface $mail
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Quote\Model\MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param Referral $referralModel
     * @param \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel
     */
    public function __construct(
        \Magento\Customer\Model\Session                          $customer,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Framework\Filesystem                            $fileSystem,
        \Magento\Wishlist\Model\Wishlist                         $wishlist,
        \Magento\Catalog\Api\ProductRepositoryInterface          $productRepository,
        \Magento\Catalog\Helper\Image                            $imageHelper,
        \Magento\Checkout\Model\Session                          $checkoutSession,
        \Magento\Quote\Model\Cart\CartTotalRepository            $cart,
        \Bananacode\Kip\Block\Main                               $kip,
        \Magento\Authorization\Model\CompositeUserContext        $userContext,
        \Magento\Quote\Api\CartRepositoryInterface               $cartRepository,
        \Magento\Integration\Model\Oauth\TokenFactory            $tokenFactory,

        \Magento\InventorySales\Model\GetProductSalableQty       $getProductSalableQty,
        \Magento\Store\Model\StoreManagerInterface               $storeManager,
        \Magento\InventorySalesApi\Api\StockResolverInterface    $stockResolver,

        \Magento\Catalog\Helper\Product\Configuration            $productConfig,
        \Magento\Cms\Model\PageFactory                           $pageFactory,
        \Bananacode\Kip\Block\CustomBlocks                       $customBlocks,
        \Magento\Widget\Model\Template\FilterEmulate             $template,

        LSR                                                      $lsr,
        Data                                                     $lsData,
        ItemHelper                                               $lsItemHelper,
        LoyaltyHelper                                            $loyaltyHelper,
        BasketHelper                                             $basketHelper,
        MailInterface                                            $mail,

        \Magento\Framework\Data\Form\FormKey                     $formKey,

        \Magento\Quote\Model\MaskedQuoteIdToQuoteId              $maskedQuoteIdToQuoteId,
        \Magento\Framework\App\State                             $appState,
        \Magento\Store\Model\App\Emulation                       $emulation,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,

        Referral $referralModel,
        \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel,
        \Bananacode\Kip\Helper\Notify $bananaNotify
    ) {
        $this->_fileSystem = $fileSystem;
        $this->_wishlist = $wishlist;
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_cart = $cart;
        $this->_productConfig = $productConfig;

        $this->_customer = $customer;
        $this->_customerRepository = $customerRepository;
        $this->_userContext = $userContext;
        $this->_cartRepository = $cartRepository;
        $this->_tokenFactory = $tokenFactory;

        $this->_getProductSalableQty = $getProductSalableQty;
        $this->_storeManager = $storeManager;
        $this->_stockResolver = $stockResolver;
        $this->mail = $mail;

        $this->_kip = $kip;

        $this->loyaltyHelper = $loyaltyHelper;
        $this->basketHelper = $basketHelper;
        $this->lsr = $lsr;
        $this->lsData = $lsData;
        $this->lsItemHelper = $lsItemHelper;

        $this->_pageFactory = $pageFactory;
        $this->_customBlocks = $customBlocks;
        $this->_template = $template;

        $this->_formKey = $formKey;

        $this->_maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->_appState = $appState;
        $this->_emulation = $emulation;

        $this->_stockItemRepository = $stockItemRepository;

        $this->_referralResourceModel = $referralResourceModel;
        $this->_referralModel = $referralModel;

        $this->_bananaNotify = $bananaNotify;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/kip.log');
        $this->_logger = new \Monolog\Logger('Kip');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Get current customer logged in
     *
     * @return mixed
     */
    public function currentCustomer()
    {
        try {
            return $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }

    /**
     * Get current customer logged in conctact data
     *
     * @return mixed
     */
    public function currentCustomerContactData()
    {
        try {
            if ($id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
                $customer = $this->_customerRepository->getById($id);

                $binsBAC = $this->_kip->getConfig('payment/bacfac/bac_bins_numbers');
                $binsCUS = $this->_kip->getConfig('payment/bacfac/cus_bins_numbers');
                $binsPROME = $this->_kip->getConfig('payment/bacfac/prome_bins_numbers');
                $binsAGRICOLA = $this->_kip->getConfig('payment/bacfac/agricola_bins_numbers');


                $exemptProductId = $this->_kip->getConfig('payment/bacfac/exempt_product_id');
                $exempt = null;
                try {
                    $exempt = $this->_productRepository->getById($exemptProductId);
                } catch (\Exception $e) {
                }

                $this->jsonResponse([
                    "response" => 200,
                    "data" => [
                        "id" => $id,
                        "formkey" => $this->_formKey->getFormKey(),
                        "avatar" => $customer->getCustomAttribute('avatar') ? $customer->getCustomAttribute('avatar')->getValue() : '',
                        "firstname" => $customer->getFirstname(),
                        "lastname" => $customer->getLastname(),
                        "email" => $customer->getEmail(),
                        "telephone" => $customer->getCustomAttribute('telephone') ? $customer->getCustomAttribute('telephone')->getValue() : '8888-8888',
                        "global" => [
                            "bins" => $binsBAC . ',' . $binsCUS . ',' . $binsPROME . ',' . $binsAGRICOLA,
                            "exempt" => $exempt ? $exempt->getSku() : '',
                        ]
                    ]
                ]);
            } else {
                $this->jsonResponse([
                    "response" => 200,
                    "data" => [
                        "id" => 0,
                        "formkey" => $this->_formKey->getFormKey(),
                        "name" => "Cliente No Registrado",
                        "email" => "anonimo@email.com",
                        "telephone" => "8888-8888",
                    ]
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }

    /**
     * Update customer avatar
     *
     * @param string $base64
     * @param string $id
     * @return mixed
     */
    public function avatar($base64, $id)
    {
        if ($id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            $mediaPath = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
            $media = $mediaPath . 'avatars/';
            $data = $base64;
            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);
            $filename = $id . '-' . uniqid() . '.png';
            if (file_put_contents($media . $filename, $data)) {
                try {
                    $customer = $this->_customerRepository->getById($id);
                    $customer->setCustomAttribute('avatar', '/pub/media/avatars/' . $filename);
                    $this->_customerRepository->save($customer);
                    $this->jsonResponse([
                        "src" => '/pub/media/avatars/' . $filename
                    ]);
                } catch (\Exception $e) {
                }
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
        return '';
    }

    /**
     * Report site issue
     *
     * @param string $data
     * @return mixed
     */
    public function report($data)
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            $this->sendEmail($this->validatedParams(json_decode($data, true)));
            $this->jsonResponse([
                "response" => 200
            ]);
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * Update customer experience
     *
     * @param string $data
     * @return mixed
     */
    public function experience($data)
    {
        try {
            if ($id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
                $customer = $this->_customerRepository->getById($id);
                $customer->setCustomAttribute('experience', $data);
                $this->_customerRepository->save($customer);
                $this->jsonResponse([
                    "response" => 200
                ]);
            } else {
                http_response_code(401);
                $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300
            ]);
        }
    }


    /**
     * Verify if product is on customer wishlist
     *
     * @param integer $product_id
     * @return mixed
     */
    public function isWished($product_id)
    {
        if ($customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                if ($wishlist = $this->_wishlist->loadByCustomerId($customerId, true)) {
                    $item = $this->_kip->getWishlistItems($wishlist->getId(), $product_id);
                    if ($item) {
                        if (!empty($item['description'])) {
                            $this->jsonResponse([
                                "status" => 200,
                                "isWished" => true
                            ]);
                        } else {
                            $this->jsonResponse([
                                "status" => 200,
                                "isWished" => false
                            ]);
                        }
                    } else {
                        $this->jsonResponse([
                            "status" => 200,
                            "isWished" => false
                        ]);
                    }
                } else {
                    $this->jsonResponse([
                        "status" => 200,
                        "isWished" => false
                    ]);
                }
            } catch (\Exception $e) {
                $this->jsonResponse([
                    "response" => 300,
                    "error" => $e->getMessage()
                ]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * Get current customer wish lists
     *
     * @return mixed
     */
    public function wishLists()
    {
        if ($customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                if ($wishlist = $this->_wishlist->loadByCustomerId($customerId, true)) {
                    $items = $this->_kip->getWishlistItems($wishlist->getId());
                    $lists = [];
                    foreach ($items as $item) {
                        $product = $this->_productRepository->getById($item['product_id']);
                        foreach (explode(',', $item['description']) as $l) {
                            if (!isset($lists[$l])) {
                                /*$image_url = $this->_imageHelper
                                    ->init($product, 'product_page_image_small')
                                    ->setImageFile($product->getImage())
                                    ->resize(200, 200)
                                    ->getUrl();*/

                                $lists[$l] = [
                                    //"thumb" => $image_url,//$image_url,
                                    "value" => $l,
                                    "count" => 1,
                                    "products" => [$product->getId()],
                                    "skus" => [$product->getSku()],
                                ];
                            } else {
                                $lists[$l]["count"] += 1;
                                $lists[$l]["products"][] = $product->getId();
                                $lists[$l]["skus"][] = $product->getSku();
                            }
                        }
                    }

                    $this->jsonResponse([
                        "status" => 200,
                        "output" => array_values($lists)
                    ]);
                }
            } catch (\Exception $e) {
                $this->jsonResponse([
                    "response" => 300,
                    "error" => $e->getMessage()
                ]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * Add product to customer wishlist
     *
     * @param integer $product_id
     * @param string $name
     * @return mixed
     */
    public function addToWishList($product_id, $name)
    {
        if ($customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                if ($wishlist = $this->_wishlist->loadByCustomerId($customerId, true)) {
                    /**
                     * @var \Magento\Wishlist\Model\Item $item ;
                     */
                    $items = $this->_kip->getWishlistItems($wishlist->getId(), $product_id);
                    $itemId = isset($item['wishlist_item_id']) ? $item['wishlist_item_id'] : null;

                    //If not item then add new one
                    if (!$items || count($items) < 1) {
                        /**
                         * @var \Magento\Wishlist\Model\Item $item ;
                         */
                        $buyRequest = new \Magento\Framework\DataObject(['qty' => 1]);
                        $item = $wishlist->addNewItem($product_id, $buyRequest);
                    } else {
                        $item = $itemId ? $wishlist->getItem($itemId) : null;
                    }

                    if ($item) {
                        if (!empty($item['description'])) {
                            $lists = explode(',', $item['description']);
                            if (($key = array_search($name, $lists)) !== false) {
                                unset($lists[$key]);
                                $list = implode(',', $lists);
                            } else {
                                $list = $item['description'] . ',' . $name;
                            }

                            if (empty($list)) {
                                $item->delete();
                            } else {
                                $item->setDescription($list)->save();
                            }
                        } else {
                            $list = $name;
                            $item->setDescription($list)->save();
                        }

                        $this->jsonResponse($list);
                    } else {
                        $this->_kip->deleteWishlistItem($itemId);
                        $this->jsonResponse("");
                    }
                }
            } catch (\Exception $e) {
                $this->jsonResponse([
                    "response" => 300,
                    "error" => $e->getMessage(),
                    "line" => $e->getLine(),
                    "backtrace" => $e->getTraceAsString()
                ]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }

        $this->jsonResponse([
            "response" => 300,
            "error" => 'Error'
        ]);
    }

    /**
     * Delete product customer wishlist
     *
     * @param string $name
     * @return mixed
     */
    public function deleteWishList($name)
    {
        if ($customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                if ($wishlist = $this->_wishlist->loadByCustomerId($customerId, true)) {
                    $items = $this->_kip->getWishlistItems($wishlist->getId());
                    /**
                     * @var \Magento\Wishlist\Model\Item $item ;
                     */
                    foreach ($items as $item) {
                        $lists = explode(',', $item['description']);
                        $result = $item['description'];
                        if (($key = array_search($name, $lists)) !== false) {
                            unset($lists[$key]);
                            $result = implode(',', $lists);
                        }

                        $loadedItem = $wishlist->getItem($item['wishlist_item_id']);
                        if ($loadedItem) {
                            if (empty($result)) {
                                $loadedItem->delete();
                            } else {
                                $loadedItem->setDescription($result)->save();
                            }
                        }
                    }
                    $this->jsonResponse(false);
                }
            } catch (\Exception $e) {
                $this->jsonResponse([
                    "response" => 300,
                    "error" => $e->getMessage(),
                    "line" => $e->getLine()
                ]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * Get current customer recurring products
     *
     * @return mixed
     */
    public function recurring()
    {
        try {
            if ($customer_id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
                $this->jsonResponse([
                    "products_ids" => $this->_kip->recurringProducts($customer_id, true),
                ]);
            } else {
                http_response_code(401);
                $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }

    /**
     * Return main categories tree
     *
     * @return mixed
     */
    public function categories()
    {
        try {
            $this->jsonResponse($this->_kip->getCategoryTreeJSON());
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }


    /**
     * Get available offers
     *
     * @return mixed
     */
    public function offers()
    {
        try {
            if ($id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
                $customer = $this->_customerRepository->getById($id);
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                /**
                 * @var $_lsCustomerFactory \Magento\Customer\Model\CustomerFactory
                 */
                $_customerFactory = $om->get('\Magento\Customer\Model\CustomerFactory');
                /**
                 * @var $_offersHelper \Bananacode\Ls\Block\Loyalty\Offers
                 */
                $_offersHelper = $om->get('\Bananacode\Ls\Block\Loyalty\Offers');
                $lsCustomer =
                    $_customerFactory->create()
                    ->setWebsiteId($customer->getWebsiteId())
                    ->loadByEmail($customer->getEmail());

                $response = null;
                // @codingStandardsIgnoreLine
                $request = new Operation\PublishedOffersGetByCardId();
                $request->setToken($lsCustomer->getData('lsr_token'));
                // @codingStandardsIgnoreLine
                $entity = new Entity\PublishedOffersGetByCardId();
                $entity->setCardId($lsCustomer->getData('lsr_cardid'));
                $entity->setItemId('');

                try {
                    $response = $request->execute($entity);
                } catch (\Exception $e) {
                    $this->jsonResponse([
                        "response" => 300,
                        "error" => $e->getMessage()
                    ]);
                }
                $offers = $response ? $response->getResult() : $response;

                $filteredOffers = [];
                foreach ($offers as $offer) {
                    $offerEntity = $_offersHelper->getOfferProductCategoryLink($offer->getOfferLines()->getPublishedOfferLine());
                    $offerImages = $_offersHelper->fetchImages($offer);
                    if ($offerEntity[0]) {
                        $offerData = [];
                        if (count($offerImages) && !empty($offerImages[0])) {
                            $offerData['image'] = $offerImages[0];
                        }
                        $offerData['description'] = $offer->getDescription();
                        $offerData['code'] = $offer->getCode();
                        $offerData['coupon'] = $offer->getOfferId();
                        $offerData['expiration'] = $offer->getExpirationDate();
                        $offerData['details'] = $offer->getDetails();
                        $offerData['entity_type'] = $offerEntity[1];
                        $offerData['url'] = $offerEntity[2];
                        $offerData['entity_id'] = $offerEntity[1] === 'product' ? $offerEntity[0]->getSku() : $offerEntity[0]->getId();
                        $filteredOffers[] = $offerData;
                    }
                }

                $this->jsonResponse([
                    "offers" => $filteredOffers
                ]);
            } else {
                http_response_code(401);
                $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }

    /**
     * Get dynamic banners
     *
     * @return mixed
     */
    public function banners()
    {
        try {
            /**
             * @var $div \DOMNode
             * @var $child \DOMText
             */
            $inspirations = [];
            $homepage = $this->_pageFactory->create()->load('home');
            $dom = new \DOMDocument();
            $dom->loadHTML('<?xml encoding="UTF-8">' . $this->_template->filter($homepage->getContent()));
            $divs = $dom->getElementsByTagName('div');
            foreach ($divs as $i => $div) {
                if ($div->getAttribute('class') == 'card') {
                    foreach ($div->childNodes as $child) {
                        if (method_exists($child, 'getAttribute')) {
                            if (!empty($child->textContent)) {
                                $inspirations[$i]['copies'][] = $child->textContent;
                            }
                            if (!empty($child->getAttribute('href'))) {
                                $inspirations[$i]['link'] = $child->getAttribute('href');
                            }
                            if (!empty($child->getAttribute('data-src'))) {
                                $inspirations[$i]['image'] = $child->getAttribute('data-src');
                            }
                            if (!empty($child->getAttribute('data-category'))) {
                                $inspirations[$i]['category_id'] = $child->getAttribute('data-category');
                            }
                        }
                    }
                }
            }

            $this->_emulation->startEnvironmentEmulation(1, 'frontend', true);
            $banners = $this->emulateFrontendBanners();
            $this->_emulation->stopEnvironmentEmulation();

            $this->jsonResponse([
                "status" => 200,
                "banners" => $banners,
                "inspirations" => array_values($inspirations)
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }

    /**
     * Get season skus
     *
     * @return mixed
     */
    public function season()
    {
        try {
            $season = [];
            $temporada = $this->_pageFactory->create()->load('temporada');
            $this->extractHtmlSeasonSkus($temporada->getContent(), $season);
            $this->jsonResponse([
                "status" => 200,
                "season" => $season
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }
    }

    /**
     * @return array
     */
    private function emulateFrontendBanners()
    {
        $banners = [];

        $homepage = $this->_pageFactory->create()->load('home');
        $this->extractBanners($homepage->getContent(), 'home', $banners);

        $season = $this->_pageFactory->create()->load('temporada');
        $this->extractBanners($season->getContent(), 'temporada', $banners);

        $offers = $this->_customBlocks->getHtmlBlock('offers', true);
        $this->extractBanners($offers, 'offers', $banners);

        return $banners;
    }

    function addCoupon($quoteid, $tok, $coupon)
    {
        // $params = array ('surname' => 'Filip');
        // $query = http_build_query ($params);
        // $contextData = array (
        //             'method' => 'PUT',
        //             'header' => "Authorization: Bearer ".$tok);
        //             $this->_logger->addInfo(print_r('-------- CURL --------', true));
        //             $this->_logger->addInfo(print_r( $contextData , true));
        // $context = stream_context_create (array ( 'http' => $contextData ));
        $url = "https://des.kip.sv/rest/all/V1/guest-carts/$quoteid/coupons/$coupon";
        // $this->_logger->addInfo(print_r('-------- url --------' . $url , true));
        // $result =  file_get_contents (
        //             $url,  // page url
        //             false,
        //             $context);
        // return true;

        $method = "PUT";
        $data = "";
        $curl = curl_init();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        // curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer' . $tok,
            'Content-Type: application/json',
        ));
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if (!$result) {
            die("Connection Failure");
        }
        curl_close($curl);
        $this->_logger->addInfo(print_r('-------- url --------' . $curl, true));
        $this->_logger->addInfo(print_r('-------- url --------' . $result, true));
        return $result;
    }

    /**
     * Get current customer order summary
     *
     * @return mixed
     */
    public function orderSummary()
    {
        try {
            if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
                $quote = $this->_checkoutSession->getQuote();
                $totals = $this->_cart->get($quote->getId());
                $this->jsonResponse([
                    "subtotal" => $totals->getSubtotal(),
                    "discount" => $totals->getDiscountAmount(),
                    "discount_code" => $totals->getCouponCode() ? '(' . $totals->getCouponCode() . ')' : '',
                    "points" => - ($quote->getLsPointsSpent() * $this->loyaltyHelper->getPointRate()),
                    "referral" => $this->_checkoutSession->getReferralPoints(),
                    "shipping" => $totals->getBaseShippingAmount(),
                    "total" => $totals->getBaseGrandTotal(),
                ]);
            } else {
                http_response_code(401);
                $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                "response" => 300,
                "error" => $e->getMessage()
            ]);
        }

        $this->jsonResponse([
            "subtotal" => 0,
            "discount" => 0,
            "discount_code" => '',
            "points" => 0,
            "referral" => 0,
            "shipping" => 0,
            "total" => 0
        ]);
    }

    public function kipimpulsev1()
    {
        $status = 200;
        $response = [
            "response" => $status,
            "impulse" => $this->prepareImpulseProducts()
        ];

        $this->jsonResponse($response);
    }

    public function cleanCart($data)
    {
        $this->_logger->addInfo(print_r($data, true));
        $kcis = json_decode($data, true);
        $this->_logger->addInfo(print_r('QUOTEID=' . $kcis["id"], true));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('quote_item');
        $sql = "Delete FROM " . $tableName . " Where item_id >0  And quote_id=" . $kcis["id"];
        $connection->query($sql);
    }


    public function kipimpulsev2($data)
    {       
        $cartACT = array();
        $cart = $this->kipcart_internal($data);
      
        foreach ($cart['cart'] as $key => $value) {
            // echo $key;
            array_push($cartACT, $key);
            // print_r($item);
            // print_r(array_keys($item));
            //170686
        }


        // foreach($items as $item) {       
        //     array_push($actualCartSkus, $item->getSku());                 
        // }


        $response = [
            "impulse" => $this->prepareImpulseProductsv2($cartACT)
        ];

        $this->jsonResponse($response);
    }
    public function deleteByDB($quoteId, $itemDelete)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

        $connection = $resource->getConnection();

        $tableName = $resource->getTableName('quote_item');
        $sql = "Delete FROM " . $tableName . " Where item_id = " . $itemDelete . " And quote_id=" . $quoteId;
        $connection->query($sql);
    }
    /**
     * Update quote cart
     * @param string $data
     * @return mixed
     */
    public function kipcart_internal($data)
    {
        $time_start = microtime(true);
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {

            $this->_logger->addInfo(print_r('-------- MAGCART --------', true));
            $appCall = false;
            $quote = null;
            $firstItem = null;
            $status = 300;
            $kcis = json_decode($data, true);
            $bin = NULL;
            $referral = NULL;
            $rema_express = 0;
            $rema_flash = 0;

            /**
             * App calls
             */
            if (is_array($kcis)) {
                if (isset($kcis['bin'])) {
                    $bin = $kcis['bin'];
                    $kcis = "backToFront";
                    if (isset($kcis['quote_id'])) {
                        $appCall = true;
                        $firstItem['quote_id'] = $kcis['quote_id'];
                    }
                } else {
                    if (isset($kcis['referral'])) {
                        $referral = floatval($kcis['referral']);
                        $kcis = "backToFront";
                        if (isset($kcis['quote_id'])) {
                            $appCall = true;
                            $firstItem['quote_id'] = $kcis['quote_id'];
                        }
                    } else {
                        if (isset(array_values($kcis)[0])) {
                            $firstItem = array_values($kcis)[0];
                            if (isset($firstItem['quote_id'])) {
                                $appCall = true;
                            }
                        }
                    }
                }
                if (isset($kcis['cpcode'])) {
                    $this->_logger->addInfo(print_r('-------- CHECK COUPON --------', true));
                    $token = $customerToken->createCustomerToken($this->_customer->getId())->getToken();
                    $this->_logger->addInfo(print_r('-------- tokk --------' . $token, true));
                    $qid = $kcis['quote_id'];
                    $this->_logger->addInfo(print_r('-------- qu --------' . $qid, true));
                    $this->_logger->addInfo(print_r('-------- cp --------' . $kcis['cpcode'], true));
                    $this->addCoupon($qid, $token, $kcis['cpcode']);
                }
            } else {
                if ($data != "backToFront") {
                    $firstItem = [];
                    $firstItem['quote_id'] = $data;
                    $appCall = true;
                }
            }

            //Check if user logged in but token has expired
            if (!$this->_userContext->getUserId() && $this->_customer->getId()) {
                if ($appCall) {
                    http_response_code(401);
                    $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
                }

                $customerToken = $this->_tokenFactory->create();
                $token = $customerToken->createCustomerToken($this->_customer->getId())->getToken();
                $this->jsonResponse([
                    "response" => 200,
                    "token" => $token
                ]);
            }

            try {
                //Init variables
                $save = false;
                $errors = [];
                $errorsExtraData = [];
                $skusAdded = [];
                $websiteCode = $this->_storeManager->getWebsite()->getCode();
                $stockId = $this->_stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

                /**
                 * App calls
                 */
                if ($appCall && $firstItem) {
                    $cartId = $this->_maskedQuoteIdToQuoteId->execute($firstItem['quote_id']);
                    $this->_customer->setCustomerId($this->_userContext->getUserId());
                    $this->_checkoutSession->setQuoteId($cartId);
                }

                $quote = $this->_checkoutSession->getQuote();
                if (!$quote->getId() && $this->_customer->getCustomer()) {
                    $this->_logger->addInfo(print_r('Creating new cart...', true));
                    $this->_cartRepository->save($quote);
                }

                $this->_logger->addInfo(print_r($this->_customer->getCustomer()->getName() . ':' . $quote->getId(), true));

                if (is_array($kcis)) {
                    //Delete, update or add products to cart
                    foreach ($kcis as $id => &$kci) {
                        if (!$kci) {
                            continue;
                        } else {
                            unset($kcis[$id]);
                        }

                        $update = false;
                        $add = false;
                        $quoteItem = false;

                        $quoteItemId = false;
                        if (isset($kci['qi'])) {
                            $quoteItemId = $kci['qi'];
                        }

                        if ($appCall) {
                            $product = $this->_productRepository->get($id);
                            $id = $product->getId();
                        } else {
                            $product = $this->_productRepository->getById($id);
                        }

                        if ($product->isSalable()) {

                            $quoteItem = $quoteItemId ? $quote->getItemById($quoteItemId) : false;
                            if ($quote->hasProductId(intval($id)) && gettype($quoteItem) == 'object') {
                                //Delete or update
                                if ($kci['q'] <= 0) {
                                    $this->_logger->addInfo(print_r('delete: ' . $product->getName(), true));
                                    try {
                                        unset($kcis[$id]);
                                        $quote->deleteItem($quoteItem);
                                        $save = true;
                                    } catch (\Exception $e) {
                                        $this->_logger->addError(print_r($e->getMessage(), true));
                                        $errors[] = __('Error eliminando "%1" del carrito.', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                } else {
                                    if ($quoteItem->getTotalQty() != $kci['q']) {
                                        $update = true;
                                    }
                                }
                            } else {
                                //Add
                                if ($kci['q'] > 0) {
                                    $add = true;
                                } else {
                                    unset($kcis[$id]);
                                }
                            }

                            //Add or update qty
                            $qtyLeft = 0;
                            if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                                $stockData = null;
                                try {
                                    $stockData = $this->_stockItemRepository->get($product->getExtensionAttributes()->getStockItem()->getItemId());
                                } catch (\Exception $e) {
                                }
                                if ($stockData) {
                                    if (!$stockData->getManageStock()) {
                                        $qtyLeft = $kci['q'];
                                    } else {
                                        $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
                                    }
                                } else {
                                    $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
                                }
                            } else {
                                if (gettype($quoteItem) == 'object') {
                                    $childProduct = $this->_productRepository->getById($quoteItem->getBuyRequest()->getData('selected_configurable_option'));
                                    if ($childProduct) {
                                        $stockData = null;
                                        try {
                                            $stockData = $this->_stockItemRepository->get($product->getExtensionAttributes()->getStockItem()->getItemId());
                                        } catch (\Exception $e) {
                                        }
                                        if ($stockData) {
                                            if (!$stockData->getManageStock()) {
                                                $qtyLeft = $kci['q'];
                                            } else {
                                                $qtyLeft = $this->_getProductSalableQty->execute($childProduct->getSku(), $stockId);
                                            }
                                        } else {
                                            $qtyLeft = $this->_getProductSalableQty->execute($childProduct->getSku(), $stockId);
                                        }
                                    }
                                }
                            }

                            if ($update || $add) {
                                if ($kci['q'] > $qtyLeft) {
                                    $kci['q'] = $qtyLeft;
                                    if ($kci['q'] > 0) {
                                        $errors[] = __('No hay suficiente "%1", hemos agregado %2 a tu carrito.', $product->getName(), $kci['q']);
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    } else {
                                        $errors[] = __('No hay "%1" en inventario.', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                        $add = false;
                                        $update = false;
                                    }
                                } else {
                                    $minQty = $this->_kip->getMinSale($product);
                                    if ($kci['q'] < $minQty) {
                                        $kci['q'] = $minQty;
                                        $errors[] = __('No puedes agregar menos de %1 "%2", hemos agregado el mínimo a tu carrito.', $kci['q'], $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                }

                                if ($add) {
                                    $this->_logger->addInfo(print_r('add: ' . $product->getName() . ' : ' . $kci['q'], true));
                                    try {
                                        $skusAdded[] = $product->getSku();
                                        $configurable = isset($kci['c']) ? ($kci['c'] ?? false) : false;
                                        if ($configurable) {
                                            $kci['c']['qty'] = $kci['q'];
                                            $parentProduct = $this->_productRepository->getById($kci['c']['product']);
                                            $quote->addProduct($parentProduct, new \Magento\Framework\DataObject($kci['c']));
                                            unset($kcis[$id]);
                                        } else {
                                            $quote->addProduct($product, new \Magento\Framework\DataObject([
                                                'product' => $id,
                                                'qty' => $kci['q'],
                                            ]));
                                        }
                                    } catch (\Exception $e) {
                                        $this->_logger->addInfo(print_r($e->getMessage(), true));
                                        unset($kcis[$id]);
                                        $errors[] = __('Error agregando "%1" al carrito.', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                    $save = true;
                                }

                                if ($update) {
                                    $this->_logger->addInfo(print_r('update: ' . $product->getName() . ' : ' . $kci['q'], true));
                                    try {
                                        $buyRequest = $quoteItem->getBuyRequest()->toArray();
                                        $buyRequest['qty'] = $kci['q'];
                                        $buyRequest['id'] = $quoteItem->getItemId();
                                        $quote->updateItem($quoteItem->getItemId(), new \Magento\Framework\DataObject($buyRequest));
                                    } catch (\Exception $e) {
                                        $this->_logger->addError(print_r($e->getMessage(), true));
                                        unset($kcis[$id]);
                                        $errors[] = __('Error actualizando el producto "%1".', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                    $save = true;
                                }
                            } else {
                                //Check if old products are still in stock
                                if (gettype($quoteItem) == 'object') {
                                    if ($qtyLeft < $quoteItem->getTotalQty() && $qtyLeft > 0) {
                                        $quote->updateItem($quoteItem->getItemId(), new \Magento\Framework\DataObject([
                                            'product' => $id,
                                            'qty' => $qtyLeft,
                                            'id' => $quoteItem->getItemId()
                                        ]));
                                        $save = true;
                                        $errors[] = __('No hay suficiente "%1", hemos agregado %2 a tu carrito.', $product->getName(), $qtyLeft);
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                }
                            }
                        } else {
                            unset($kcis[$id]);
                            $errors[] = __('No hay "%1" en inventario.- Lo hemos eliminado de tu carrito.', $product->getName());
                            $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                        }
                    }
                } else {
                    $kcis = [];
                    $save = true;
                }

                $status = 200;
            } catch (\Exception $e) {
                $this->_logger->addError(print_r($e->getMessage(), true));
                if ($e->getMessage() == 'Invalid state change requested') {
                    $customerToken = $this->_tokenFactory->create();
                    $token = $customerToken->createCustomerToken($this->_customer->getId())->getToken();
                    $this->jsonResponse([
                        "response" => 200,
                        "token" => $token
                    ]);
                } else {
                    $errors[] = __('Error actualizando tu carrito: %1.', $e->getMessage());
                    $errorsExtraData[] = 'Cliente: ' . $this->_customer->getCustomer()->getName();
                    $status = 300;
                }
            }

            $referralTotal = 0;
            if ($quote) {
                if (is_numeric($referral)) {
                    $referralTotal = $this->applyReferralPoints($referral, $quote->getCustomerId());
                    $save = true;
                } else {
                    $couponToApplyBefore = $quote->getCouponCode();
                    $couponToApplyNow = $this->getCurrentCoupon($quote, $bin);
                    if ($couponToApplyBefore != $couponToApplyNow) {
                        $quote->setCouponCode($couponToApplyNow);
                        $save = true;
                    }
                }
            }

            if ($save) {
                $this->_cartRepository->save($quote);
            }

            unset($quoteItem);
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteProduct = $quoteItem->getProduct();
                $finalProduct = $quoteProduct;
                try {
                    if ($quoteItem->getBuyRequest()->getData('selected_configurable_option')) {
                        $childProduct = $this->_productRepository->getById($quoteItem->getBuyRequest()->getData('selected_configurable_option'));
                        if ($childProduct) {
                            $finalProduct = $childProduct;
                        }
                    }
                } catch (\Exception $e) {
                }

                if (isset($kcis[$finalProduct->getId()])) {
                    $description = !empty($kcis[$finalProduct->getId()]['d']) ? $kcis[$finalProduct->getId()]['d'] : $this->_kip->getPresentacion($quoteProduct, false, $this->_productConfig->getOptions($quoteItem), false, true);
                    $image = !empty($kcis[$finalProduct->getId()]['i']) ? $kcis[$finalProduct->getId()]['i'] : $this->_kip->getProductImageHtml(null, $quoteProduct, true);
                    $name = !empty($kcis[$finalProduct->getId()]['n']) ? $kcis[$finalProduct->getId()]['n'] : $quoteProduct->getName();
                } else {
                    $image = $this->_kip->getProductImageHtml(null, $quoteProduct, true);
                    $name = $quoteProduct->getName();
                    $description = $this->_kip->getPresentacion($quoteProduct, false, $this->_productConfig->getOptions($quoteItem), false, true);
                }

                $associativeId = $finalProduct->getId();
                if ($appCall) {
                    $associativeId = $finalProduct->getSku();
                }

                $v1 = "";
                $v2 = "";
                // $product = $finalProduct;
                // if($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
                //     $productTypeInstance = $product->getTypeInstance();
                //     $usedProducts = $productTypeInstance->getUsedProducts($product);                
                //     foreach ($usedProducts  as $child) {
                //         $v1=$child->getId();
                //         $v2=$child->getName();
                //         //  echo $child->getId()."</br>"; 
                //          $this->_logger->addInfo(print_r('-------- GETTING CONFIGURABLE PRODUCUTS0----------. --------'.$child, true));
                //         //  echo $child->getName()."</br>"; 
                //     }
                // }
                // else{
                //     // $product->getTypeId()
                //     $this->_logger->addInfo(print_r('-------- NOT CONFIGURABLE ----------. --------'.$product->getTypeId(), true));
                // }
                // $om = \Magento\Framework\App\ObjectManager::getInstance();
                // $stockData = $quoteProduct->isAvailable();
                // $StockState = $om->get('\Magento\CatalogInventory\Model\Stock\StockItemRepository');
                // $StockState->get($quoteProduct->getId());
                // $StockState->getIsInStock();
                // $product = $this->productRepository->get('simple-out-of-stock', true, null, true);
                // $product = $this->_productRepository->get($quoteProduct->getId());
                // $stockItem = $product->getExtensionAttributes()->getStockItem();
                // $this->assertFalse($stockItem->getIsInStock());
                //aca codity
                // $childProduct = $this->_productRepository->getById($quoteItem->getBuyRequest()->getData('selected_configurable_option'));

                // $this->_logger->addInfo(print_r('-------- 1PRODUCT: '.$product->getTypeId(), true));

                //$product = $this->_productRepository->get($quoteProduct->getId());
                //$this->_logger->addInfo(print_r('-------- 2PRODUCT: '.$quoteProduct->getId(), true));

                // $stockItem = $product->getExtensionAttributes()->getStockItem();
                // $this->_logger->addInfo(print_r('2-------- GET STOCK PRODUCT CODITY --------', true));
                // $this->_logger->addInfo(print_r('2---------------->'.$product->get_stock_quantity(), true));            
                // $this->_logger->addInfo(print_r('2---------------->'.$product->getStatus(), true));

                $kcis[$associativeId] = [
                    "q" => $quoteItem->getTotalQty(), //qty
                    "p" => ($quoteItem->getRowTotal() / $quoteItem->getTotalQty()), //price
                    "m" => $this->_kip->getMinSale($quoteProduct), //min_qty
                    "i" => $image,
                    "n" => $name,
                    "d" => $description,
                    "qi" => $quoteItem->getId(),
                    "u" => "",
                    "s" => "CASE4",
                    "sku" => $quoteItem->getSku()
                ];
                //count express and flash remain for mobile
                if (str_contains($description, 'express')) {
                    $rema_express = $rema_express +  $quoteItem->getTotalQty();
                }
                if (str_contains($description, 'flash')) {
                    $rema_flash = $rema_flash +  $quoteItem->getTotalQty();
                }
            }

            $totals = $this->_cart->get($quote->getId());

            foreach ($errors as $ei => $error) {
                $extraError = (isset($errorsExtraData[$ei]) ? $errorsExtraData[$ei] : '');
                $this->_bananaNotify->discord('errors', $error . ' ' . $extraError, 'Error');
            }
            $flash_capacity = $this->_kip->getFlashLimit() ? intval($this->_kip->getFlashLimit()) : 0;
            $express_capacity = $this->_kip->getExpressLimit() ? intval($this->_kip->getExpressLimit()) : 0;
            $response = [
                "response" => $status,
                "express" => $this->_kip->getExpressLimit() ? intval($this->_kip->getExpressLimit()) : 0,
                "flash" => $this->_kip->getFlashLimit() ? intval($this->_kip->getFlashLimit()) : 0,
                "cart" => $kcis,
                "totals" => [
                    "subtotal" => $totals->getSubtotal(),
                    "discount" => $totals->getDiscountAmount(),
                    "discount_code" => $totals->getCouponCode() ? '(' . $totals->getCouponCode() . ')' : '',
                    "referral" => $referralTotal,
                    "shipping" => $totals->getBaseShippingAmount(),
                    "total" => $totals->getBaseGrandTotal(),
                    //"points" => -($quote->getLsPointsSpent() * $this->loyaltyHelper->getPointRate()),
                ],
                "quote_id" => $quote->getId(),
                "errors" => $errors,
                "impulse" => $this->prepareImpulseProducts(),
                // "impulse_v2" => [],
                // "impulse_v2" => $this->prepareImpulseProductsv2($kcis),
                "skus" => $skusAdded,
                "remain_flash" => $flash_capacity - $rema_flash,
                "remain_express" =>  $express_capacity - $rema_express,
                "total_discoints" => $referralTotal + $totals->getDiscountAmount()
            ];
            $this->_logger->addInfo(print_r('MAG: ' . number_format(microtime(true) - $time_start, 6) . 's', true));
            return $response;
            // $this->jsonResponse($response);
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }









    public function kipcart($data)
    {
        $time_start = microtime(true);
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {

            $this->_logger->addInfo(print_r('-------- MAGCART --------', true));
            $appCall = false;
            $quote = null;
            $firstItem = null;
            $status = 300;
            $kcis = json_decode($data, true);
            $bin = NULL;
            $referral = NULL;
            $rema_express = 0;
            $rema_flash = 0;

            /**
             * App calls
             */
            if (is_array($kcis)) {
                if (isset($kcis['bin'])) {
                    $bin = $kcis['bin'];
                    $kcis = "backToFront";
                    if (isset($kcis['quote_id'])) {
                        $appCall = true;
                        $firstItem['quote_id'] = $kcis['quote_id'];
                    }
                } else {
                    if (isset($kcis['referral'])) {
                        $referral = floatval($kcis['referral']);
                        $kcis = "backToFront";
                        if (isset($kcis['quote_id'])) {
                            $appCall = true;
                            $firstItem['quote_id'] = $kcis['quote_id'];
                        }
                    } else {
                        if (isset(array_values($kcis)[0])) {
                            $firstItem = array_values($kcis)[0];
                            if (isset($firstItem['quote_id'])) {
                                $appCall = true;
                            }
                        }
                    }
                }
                if (isset($kcis['cpcode'])) {
                    $customerToken = $this->_tokenFactory->create();
                    $this->_logger->addInfo(print_r('-------- CHECK COUPON --------', true));
                    $token = $customerToken->createCustomerToken($this->_customer->getId())->getToken();
                    $this->_logger->addInfo(print_r('-------- tokk --------' . $token, true));
                    $qid = $kcis['quote_id'];
                    $this->_logger->addInfo(print_r('-------- qu --------' . $qid, true));
                    $this->_logger->addInfo(print_r('-------- cp --------' . $kcis['cpcode'], true));
                    $this->addCoupon($qid, $token, $kcis['cpcode']);
                }
            } else {
                if ($data != "backToFront") {
                    $firstItem = [];
                    $firstItem['quote_id'] = $data;
                    $appCall = true;
                }
            }

            //Check if user logged in but token has expired
            if (!$this->_userContext->getUserId() && $this->_customer->getId()) {
                if ($appCall) {
                    http_response_code(401);
                    $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
                }

                $customerToken = $this->_tokenFactory->create();
                $token = $customerToken->createCustomerToken($this->_customer->getId())->getToken();
                $this->jsonResponse([
                    "response" => 200,
                    "token" => $token
                ]);
            }

            try {
                //Init variables
                $save = false;
                $errors = [];
                $errorsExtraData = [];
                $skusAdded = [];
                $websiteCode = $this->_storeManager->getWebsite()->getCode();
                $stockId = $this->_stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

                /**
                 * App calls
                 */
                if ($appCall && $firstItem) {
                    $cartId = $this->_maskedQuoteIdToQuoteId->execute($firstItem['quote_id']);
                    $this->_customer->setCustomerId($this->_userContext->getUserId());
                    $this->_checkoutSession->setQuoteId($cartId);
                }

                $quote = $this->_checkoutSession->getQuote();
                if (!$quote->getId() && $this->_customer->getCustomer()) {
                    $this->_logger->addInfo(print_r('Creating new cart...', true));
                    $this->_cartRepository->save($quote);
                }

                $this->_logger->addInfo(print_r($this->_customer->getCustomer()->getName() . ':' . $quote->getId(), true));

                if (is_array($kcis)) {
                    //Delete, update or add products to cart
                    foreach ($kcis as $id => &$kci) {
                        if (!$kci) {
                            continue;
                        } else {
                            unset($kcis[$id]);
                        }

                        $update = false;
                        $add = false;
                        $quoteItem = false;

                        $quoteItemId = false;
                        if (isset($kci['qi'])) {
                            $quoteItemId = $kci['qi'];
                        }

                        if ($appCall) {
                            $product = $this->_productRepository->get($id);
                            $id = $product->getId();
                        } else {
                            $product = $this->_productRepository->getById($id);
                        }

                        // if ($product->isSalable()) {
                        // mendoza
                        $this->_logger->addError(print_r("0MENDOZA", true));
                        // $this->_logger->addError(print_r($product->isSalable(), true));
                        // $this->_logger->addError(print_r(json_encode($kcis), true));

                        if ($product->isSalable() || $kci['q'] == 0) {
                            $quoteItem = $quoteItemId ? $quote->getItemById($quoteItemId) : false;
                            if ($quote->hasProductId(intval($id)) && gettype($quoteItem) == 'object') {
                                //Delete or update
                                if ($kci['q'] <= 0) {
                                    $this->_logger->addInfo(print_r('delete: ' . $product->getName(), true));
                                    try {
                                        unset($kcis[$id]);
                                        $quote->deleteItem($quoteItem);
                                        $save = true;
                                    } catch (\Exception $e) {
                                        $this->_logger->addError(print_r($e->getMessage(), true));
                                        $errors[] = __('Error eliminando "%1" del carrito.', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                } else {
                                    if ($quoteItem->getTotalQty() != $kci['q']) {
                                        $update = true;
                                    }
                                }
                            } else {
                                //Add
                                if ($kci['q'] > 0) {
                                    $add = true;
                                } else {
                                    unset($kcis[$id]);
                                }
                            }

                            //Add or update qty
                            $qtyLeft = 0;
                            if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                                $stockData = null;
                                try {
                                    $stockData = $this->_stockItemRepository->get($product->getExtensionAttributes()->getStockItem()->getItemId());
                                } catch (\Exception $e) {
                                }
                                if ($stockData) {
                                    if (!$stockData->getManageStock()) {
                                        $qtyLeft = $kci['q'];
                                    } else {
                                        $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
                                    }
                                } else {
                                    $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
                                }
                            } else {
                                if (gettype($quoteItem) == 'object') {
                                    $childProduct = $this->_productRepository->getById($quoteItem->getBuyRequest()->getData('selected_configurable_option'));
                                    if ($childProduct) {
                                        $stockData = null;
                                        try {
                                            $stockData = $this->_stockItemRepository->get($product->getExtensionAttributes()->getStockItem()->getItemId());
                                        } catch (\Exception $e) {
                                        }
                                        if ($stockData) {
                                            if (!$stockData->getManageStock()) {
                                                $qtyLeft = $kci['q'];
                                            } else {
                                                $qtyLeft = $this->_getProductSalableQty->execute($childProduct->getSku(), $stockId);
                                            }
                                        } else {
                                            $qtyLeft = $this->_getProductSalableQty->execute($childProduct->getSku(), $stockId);
                                        }
                                    }
                                }
                            }

                            if ($update || $add) {
                                if ($kci['q'] > $qtyLeft) {
                                    $kci['q'] = $qtyLeft;
                                    if ($kci['q'] > 0) {
                                        $errors[] = __('No hay suficiente "%1", hemos agregado %2 a tu carrito.', $product->getName(), $kci['q']);
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    } else {
                                        $errors[] = __('No hay "%1" en inventario..', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                        $add = false;
                                        $update = false;
                                    }
                                } else {
                                    $minQty = $this->_kip->getMinSale($product);
                                    if ($kci['q'] < $minQty) {
                                        $kci['q'] = $minQty;
                                        $errors[] = __('No puedes agregar menos de %1 "%2", hemos agregado el mínimo a tu carrito.', $kci['q'], $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                }

                                if ($add) {
                                    $this->_logger->addInfo(print_r('add: ' . $product->getName() . ' : ' . $kci['q'], true));
                                    try {
                                        $skusAdded[] = $product->getSku();
                                        $configurable = isset($kci['c']) ? ($kci['c'] ?? false) : false;
                                        if ($configurable) {
                                            $kci['c']['qty'] = $kci['q'];
                                            $parentProduct = $this->_productRepository->getById($kci['c']['product']);
                                            $quote->addProduct($parentProduct, new \Magento\Framework\DataObject($kci['c']));
                                            unset($kcis[$id]);
                                        } else {
                                            $quote->addProduct($product, new \Magento\Framework\DataObject([
                                                'product' => $id,
                                                'qty' => $kci['q'],
                                            ]));
                                        }
                                    } catch (\Exception $e) {
                                        $this->_logger->addInfo(print_r($e->getMessage(), true));
                                        unset($kcis[$id]);
                                        $errors[] = __('Error agregando "%1" al carrito.', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                    $save = true;
                                }

                                if ($update) {
                                    $this->_logger->addInfo(print_r('update: ' . $product->getName() . ' : ' . $kci['q'], true));
                                    try {
                                        $buyRequest = $quoteItem->getBuyRequest()->toArray();
                                        $buyRequest['qty'] = $kci['q'];
                                        $buyRequest['id'] = $quoteItem->getItemId();
                                        $quote->updateItem($quoteItem->getItemId(), new \Magento\Framework\DataObject($buyRequest));
                                    } catch (\Exception $e) {
                                        $this->_logger->addError(print_r($e->getMessage(), true));
                                        unset($kcis[$id]);
                                        $errors[] = __('Error actualizando el producto "%1".', $product->getName());
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                    $save = true;
                                }
                            } else {
                                //Check if old products are still in stock
                                if (gettype($quoteItem) == 'object') {
                                    if ($qtyLeft < $quoteItem->getTotalQty() && $qtyLeft > 0) {
                                        $quote->updateItem($quoteItem->getItemId(), new \Magento\Framework\DataObject([
                                            'product' => $id,
                                            'qty' => $qtyLeft,
                                            'id' => $quoteItem->getItemId()
                                        ]));
                                        $save = true;
                                        $errors[] = __('No hay suficiente "%1", hemos agregado %2 a tu carrito.', $product->getName(), $qtyLeft);
                                        $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                                    }
                                }
                            }
                        } else {
                            try {
                                // $this->_logger->addError(print_r("1MENDOZA ANTES DE ELIMINAR", true));    
                                // $this->_logger->addError(print_r("2MENDOZA ---------->".$id, true));
                                // $this->_logger->addError(print_r("2MENDOZA quoteid ---------->".$quote->getId(), true));
                                // $this->_logger->addError(print_r("2MENDOZA productid ---------->".$quoteItemId, true));
                                //$quote->hasProductId(intval($id))
                                //$quote->getId()
                                // $this->_logger->addError(print_r($kcis, true)); 
                                $this->deleteByDB($quote->getId(), $quoteItemId);
                                //start

                                //end

                                unset($kcis[$id]); //mendoza
                                $errors[] = __('No hay "%1" en inventario,  Lo hemos eliminado de tu carrito |', $product->getName());
                                $errorsExtraData[] = 'SKU: ' . $product->getSku() . ' | Cliente: ' . $this->_customer->getCustomer()->getName();
                            } catch (\Exception $e) {
                                // $errors[] = __('No hay "%1" en inventario,  Lo he eliminado de tu carrito. ->',$e);
                                $this->_logger->addError(print_r("ERROR: ---------->" . $e, true));
                            }
                        }
                    }
                } else {
                    $kcis = [];
                    $save = true;
                }

                $status = 200;
            } catch (\Exception $e) {
                $this->_logger->addError(print_r($e->getMessage(), true));
                if ($e->getMessage() == 'Invalid state change requested') {
                    $customerToken = $this->_tokenFactory->create();
                    $token = $customerToken->createCustomerToken($this->_customer->getId())->getToken();
                    $this->jsonResponse([
                        "response" => 200,
                        "token" => $token
                    ]);
                } else {
                    $errors[] = __('Error actualizando tu carrito: %1.', $e->getMessage());
                    $errorsExtraData[] = 'Cliente: ' . $this->_customer->getCustomer()->getName();
                    $status = 300;
                }
            }

            $referralTotal = 0;
            if ($quote) {
                if (is_numeric($referral)) {
                    $referralTotal = $this->applyReferralPoints($referral, $quote->getCustomerId());
                    $save = true;
                } else {
                    $couponToApplyBefore = $quote->getCouponCode();
                    $couponToApplyNow = $this->getCurrentCoupon($quote, $bin);
                    if ($couponToApplyBefore != $couponToApplyNow) {
                        $quote->setCouponCode($couponToApplyNow);
                        $save = true;
                    }
                }
            }

            if ($save) {
                $this->_cartRepository->save($quote);
            }

            unset($quoteItem);

            $toDelete = [];
            $toDeleteSKUS = [];

            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteProduct = $quoteItem->getProduct();
                $finalProduct = $quoteProduct;
                try {
                    if ($quoteItem->getBuyRequest()->getData('selected_configurable_option')) {
                        $childProduct = $this->_productRepository->getById($quoteItem->getBuyRequest()->getData('selected_configurable_option'));
                        if ($childProduct) {
                            $finalProduct = $childProduct;
                        }
                    }
                } catch (\Exception $e) {
                }

                if (isset($kcis[$finalProduct->getId()])) {
                    $description = !empty($kcis[$finalProduct->getId()]['d']) ? $kcis[$finalProduct->getId()]['d'] : $this->_kip->getPresentacion($quoteProduct, false, $this->_productConfig->getOptions($quoteItem), false, true);
                    $image = !empty($kcis[$finalProduct->getId()]['i']) ? $kcis[$finalProduct->getId()]['i'] : $this->_kip->getProductImageHtml(null, $quoteProduct, true);
                    $name = !empty($kcis[$finalProduct->getId()]['n']) ? $kcis[$finalProduct->getId()]['n'] : $quoteProduct->getName();
                } else {
                    $image = $this->_kip->getProductImageHtml(null, $quoteProduct, true);
                    $name = $quoteProduct->getName();
                    $description = $this->_kip->getPresentacion($quoteProduct, false, $this->_productConfig->getOptions($quoteItem), false, true);
                }

                $associativeId = $finalProduct->getId();
                if ($appCall) {
                    $associativeId = $finalProduct->getSku();
                }

                $v1 = "";
                $v2 = "";



                $this->_logger->addInfo(print_r('3-------- CODITY NEWEST --------', true));
                $this->_logger->addInfo(print_r('3-------- GET STOCK PRODUCT CODITY -------- ' . $name, true));
                $this->_logger->addInfo(print_r('3---------------->' . $quoteItem->get_stock_quantity(), true));
                $this->_logger->addInfo(print_r('3---------------->' . $quoteItem->getStatus(), true));
                // $this->_logger->addInfo(print_r('3---------------->'.$quoteItem->isAvailable(), true));   
                // $this->_logger->addInfo(print_r('3---------------->'.$IsInStock, true));            

                $websiteCode = $this->_storeManager->getWebsite()->getCode();
                $stockId = $this->_stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
                $qtyLeft = $this->_getProductSalableQty->execute($quoteItem->getSku(), $stockId);
                $this->_logger->addInfo(print_r('3----------------S>' . $qtyLeft, true));
                $qtyStatus = "inStock";
                if ($qtyLeft == "0" || $qtyLeft == 0) {
                    $qtyStatus = "OutOfStock";
                    array_push($toDelete, $quoteItem->getId());
                    array_push($toDeleteSKUS, $name);
                }

                $kcis[$associativeId] = [
                    "q" => $quoteItem->getTotalQty(), //qty
                    "p" => ($quoteItem->getRowTotal() / $quoteItem->getTotalQty()), //price
                    "m" => $this->_kip->getMinSale($quoteProduct), //min_qty
                    "i" => $image,
                    "n" => $name,
                    "d" => $description,
                    "qi" => $quoteItem->getId(),
                    "u" => "",
                    "s" => $qtyStatus,
                    "sku" => $quoteItem->getSku(),
                    "ts" => strtotime($quoteItem->getCreatedAt()),
                    "qid" =>$quote->getId()
                ];

                //count express and flash remain for mobile
                if (str_contains($description, 'express')) {
                    $rema_express = $rema_express +  $quoteItem->getTotalQty();
                }
                if (str_contains($description, 'flash')) {
                    $rema_flash = $rema_flash +  $quoteItem->getTotalQty();
                }
            }

            // $this->deleteByDB($quote->getId(),$quoteItemId);

            foreach ($toDelete as $i) {
                $this->deleteByDB($quote->getId(), $i);
            }


            $totals = $this->_cart->get($quote->getId());

            foreach ($errors as $ei => $error) {
                $extraError = (isset($errorsExtraData[$ei]) ? $errorsExtraData[$ei] : '');
                $this->_bananaNotify->discord('errors', $error . ' ' . $extraError, 'Error');
            }
            $flash_capacity = $this->_kip->getFlashLimit() ? intval($this->_kip->getFlashLimit()) : 0;
            $express_capacity = $this->_kip->getExpressLimit() ? intval($this->_kip->getExpressLimit()) : 0;
            $response = [
                "response" => $status,
                "express" => $this->_kip->getExpressLimit() ? intval($this->_kip->getExpressLimit()) : 0,
                "flash" => $this->_kip->getFlashLimit() ? intval($this->_kip->getFlashLimit()) : 0,
                "cart" => $kcis,
                "deleted_names" => $toDeleteSKUS,
                "totals" => [
                    "subtotal" => $totals->getSubtotal(),
                    "discount" => $totals->getDiscountAmount(),
                    "discount_code" => $totals->getCouponCode() ? '(' . $totals->getCouponCode() . ')' : '',
                    "referral" => $referralTotal,
                    "shipping" => $totals->getBaseShippingAmount(),
                    "total" => $totals->getBaseGrandTotal(),
                    //"points" => -($quote->getLsPointsSpent() * $this->loyaltyHelper->getPointRate()),
                ],
                "quote_id" => $quote->getId(),
                "errors" => $errors,
                "impulse" => $this->prepareImpulseProducts(),
                // "impulse_v2" => [],
                "impulse_v2" => $this->prepareImpulseProductsv2($kcis),
                "skus" => $skusAdded,
                "remain_flash" => $flash_capacity - $rema_flash,
                "remain_express" =>  $express_capacity - $rema_express,
                "total_discoints" => $referralTotal + $totals->getDiscountAmount()
            ];
            $this->_logger->addInfo(print_r('MAG: ' . number_format(microtime(true) - $time_start, 6) . 's', true));

            $this->jsonResponse($response);
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }










    /**
     * Get logged in customer token
     * @return mixed
     */
    public function token()
    {
        if ($id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            $customerToken = $this->_tokenFactory->create();
            $this->jsonResponse([
                "token" => $customerToken->createCustomerToken($id)->getToken()
            ]);
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * Update LS Retail cart
     * @param string $data
     * @return mixed
     */
    public function lscart($data)
    {
        return  false;
    }

    /**
     * @param $points
     */
    private function applyReferralPoints($points, $customerId)
    {
        $pointsFilter = 0;
        $this->_referralResourceModel->load(
            $this->_referralModel,
            $customerId,
            'customer_id'
        );
        if ($this->_referralModel->getId()) {
            if ($points <= floatval($this->_referralModel->getCash())) {
                $pointsFilter = $points * -1;
            }
        }
        $this->_checkoutSession->setReferralPoints($pointsFilter);
        return floatval($pointsFilter);
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @param $bin
     * @return string|null
     */
    private function getCurrentCoupon($quote, $bin)
    {
        $couponToApply = $quote->getCouponCode();

        //BINs discount, apply only if there are no applied coupons.
        $checkApplyBin = true;
        $BINCoupon = null;

        $BACBINcoupons = $this->_kip->getConfig('payment/bacfac/bac_coupons_bins');
        $BACBINS = $this->_kip->getConfig('payment/bacfac/bac_bins_numbers');

        $CUSBINcoupons = $this->_kip->getConfig('payment/bacfac/cus_coupons_bins');
        $CUSBINS = $this->_kip->getConfig('payment/bacfac/cus_bins_numbers');

        $PROMEBINcoupons = $this->_kip->getConfig('payment/bacfac/prome_coupons_bins');
        $PROMEBINS = $this->_kip->getConfig('payment/bacfac/prome_bins_numbers');

        $AGRICOLABINcoupons = $this->_kip->getConfig('payment/bacfac/agricola_coupons_bins');
        $AGRICOLABINS = $this->_kip->getConfig('payment/bacfac/agricola_bins_numbers');

        $this->_logger->addInfo(print_r('-------- DISCOUNT VALIDATIONS START. --------', true));

        if (($quote->getSubtotal() - $quote->getSubtotalWithDiscount()) > 0) {
            $this->_logger->addInfo(print_r(' >0' . strval($quote->getSubtotal()) . '|' . strval($quote->getSubtotalWithDiscount()), true));
            $checkApplyBin = false;
        }

        if ($checkApplyBin) {
            if ($couponToApply == "" or $couponToApply == null or empty($couponToApply)) {

                if (!$BINCoupon = $this->getBINCoupon($quote, $bin, $BACBINcoupons, $BACBINS)) {
                    if (!$BINCoupon = $this->getBINCoupon($quote, $bin, $CUSBINcoupons, $CUSBINS)) {
                        if (!$BINCoupon = $this->getBINCoupon($quote, $bin, $PROMEBINcoupons, $PROMEBINS)) {
                            $BINCoupon = $this->getBINCoupon($quote, $bin, $AGRICOLABINcoupons, $AGRICOLABINS);
                        }
                    }
                }
            }
        }
        $this->_logger->addInfo(print_r(' CUPON  SELECTED ' . $BINCoupon, true));
        if ($BINCoupon) {
            $couponToApply = $BINCoupon;
            $this->_logger->addInfo(print_r('cond_1:', true));
        } else {
            // Check if clean BIN coupon.
            $this->_logger->addInfo(print_r('cond_2:', true));
            $couponToApply = $this->cleanBINCoupon($BACBINcoupons, $couponToApply);
            $couponToApply = $this->cleanBINCoupon($CUSBINcoupons, $couponToApply);
            $couponToApply = $this->cleanBINCoupon($PROMEBINcoupons, $couponToApply);
            $couponToApply = $this->cleanBINCoupon($AGRICOLABINcoupons, $couponToApply);
        }
        //-------------------------------------------------------------
        $this->_logger->addInfo(print_r('cond_2:', true));
        return $couponToApply;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @param $bin
     * @param $coupons
     * @param $bins
     * @return false|string
     */
    private function getBINCoupon($quote, $binNumber, $coupons, $bins)
    {
        $this->_logger->addInfo(print_r('------- getBinCoupon --------', true));
        $applyCoupon = false;
        if (!empty($binNumber) && !empty($coupons) && !empty($bins)) {
            //Apply
            $bins = explode(',', $bins);
            if (in_array($binNumber, $bins)) {
                $this->_logger->addInfo(print_r('1' . $applyCoupon, true));
                $coupons = explode(',', $coupons);
                foreach ($coupons as $coupon) {
                    $this->_logger->addInfo(print_r('2' . $applyCoupon, true));
                    $couponSettings = explode(':', $coupon);
                    if (count($couponSettings) == 2) {
                        $this->_logger->addInfo(print_r('3' . $applyCoupon, true));
                        $code = $couponSettings[0];
                        $min = floatval($couponSettings[1]);
                        if ($quote->getGrandTotal() >= $min) {
                            $this->_logger->addInfo(print_r('4' . $applyCoupon, true));
                            $applyCoupon = $code;
                            $this->_logger->addInfo(print_r('------- coupon found --------:' . $applyCoupon, true));
                        }
                    }
                }
            }
        }

        return $applyCoupon;
    }

    /**
     * @param $coupons
     * @param $couponToApply
     * @return null
     */
    private function cleanBINCoupon($coupons, $couponToApply)
    {
        if (!empty($coupons)) {
            $coupons = explode(',', $coupons);
            foreach ($coupons as $coupon) {
                $couponSettings = explode(':', $coupon);
                if (count($couponSettings) == 2) {
                    $code = $couponSettings[0];
                    if ($couponToApply == $code) {
                        return null;
                    }
                }
            }
        }
        return $couponToApply;
    }

    /**
     * @return array
     */
    private function prepareImpulseProducts()
    {
        $products = $this->_kip->getCheckoutImpulseProducts();
        $result = [];

        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        foreach ($products as $product) {
            $image = $this->_kip->getProductImageHtml(null, $product, true);
            $name = $product->getName();
            $description = $this->_kip->getPresentacion($product, false, [], false, true);

            // // TEST INICIO
            // // echo($product->getData('selected_configurable_option'));
            // // $childProduct = $this->_productRepository->getById($product->getData('selected_configurable_option'));
            // // echo($childProduct);
            // // TEST FIN

            // /* VARIANT PRODUCTS */
            // $v1 = "";
            // $v2 = "";
            // // if($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
            // //     $productTypeInstance = $product->getTypeInstance();
            // //     $usedProducts = $productTypeInstance->getUsedProducts($product);                
            // //     foreach ($usedProducts  as $child) {
            // //         $v1=$child->getId();
            // //         $v2=$child->getName();
            // //         echo $child->getId()."</br>"; 
            // //         echo $child->getName()."</br>"; 
            // //     }
            // // }

            // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
            // $stockItem = $stockRegistry->getStockItem($product->getId());
            // $IsInStock = $stockItem->getIsInStock();
            // if ($IsInStock) {                
            //     $v2 = "InStock";
            // }
            // else{
            //     $v2 = "OutOfStock";
            // }
            $this->_logger->addInfo(print_r('1-------- GET STOCK PRODUCT CODITY -------- ' . $name, true));
            $this->_logger->addInfo(print_r('1---------------->' . $product->get_stock_quantity(), true));
            $this->_logger->addInfo(print_r('1---------------->' . $product->getStatus(), true));
            $this->_logger->addInfo(print_r('1---------------->' . $product->isAvailable(), true));
            // $this->_logger->addInfo(print_r('1---------------->'.$IsInStock, true));            


            //$product->isAvailable()
            // $resFlag = $this->getStockFlag($quoteProduct,$quoteItem,$stockId);
            // $str = 'Hello there';
            // $new_str = str_replace('Hello', 'Hi', $str);
            $result[$product->getId()] = [
                "p" => $product->getPrice(), //price
                "m" => $this->_kip->getMinSale($product), //min_qty
                "i" => $image,
                "n" => $name,
                "d" => $description,
                "s" => "case2",
                "u" => "InStock",
                "sku" => $product->getSku(),
                "c" => $description,
            ];
        }
        $this->_logger->addInfo(print_r($result, true));
        return $result;
    }

    private function getStockFlag($quoteProduct, $quoteItem, $stockId)
    {
        // START
        $product = $quoteProduct;
        $qtyLeft = 0;
        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
            $stockData = null;
            try {
                $stockData = $this->_stockItemRepository->get($product->getExtensionAttributes()->getStockItem()->getItemId());
            } catch (\Exception $e) {
            }
            if ($stockData) {
                if (!$stockData->getManageStock()) {
                    $qtyLeft = $kci['q'];
                } else {
                    $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
                }
            } else {
                $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
            }
        } else {
            if (gettype($quoteItem) == 'object') {
                $childProduct = $this->_productRepository->getById($quoteItem->getBuyRequest()->getData('selected_configurable_option'));
                if ($childProduct) {
                    $stockData = null;
                    try {
                        $stockData = $this->_stockItemRepository->get($product->getExtensionAttributes()->getStockItem()->getItemId());
                    } catch (\Exception $e) {
                    }
                    if ($stockData) {
                        if (!$stockData->getManageStock()) {
                            $qtyLeft = $kci['q'];
                        } else {
                            $qtyLeft = $this->_getProductSalableQty->execute($childProduct->getSku(), $stockId);
                        }
                    } else {
                        $qtyLeft = $this->_getProductSalableQty->execute($childProduct->getSku(), $stockId);
                    }
                }
            }
        }
        // END

        if ($qtyLeft > 0) {
            $qtyLeft = "InStock";
        } else {
            $qtyLeft = "OutOfStock";
        }
        return $qtyLeft;
    }

    private function prepareImpulseProductsv2($actualCart)
    {
        $result = [];
        $this->_logger->addInfo(print_r('-------- CARRO ACTUAL ESTEBAN --------', true));
        $this->_logger->addInfo(print_r($actualCart, true));
        // $actualCartSkus = [];        
        // foreach($actualCart as $key=>$value)
        // {                            
        //     array_push($actualCartSkus, $key);
        // }
        $products = $this->_kip->getCheckoutImpulseProductsv2($actualCart);

        foreach ($products as $product) {
            $v1 = "";
            $v2 = "";
            $this->_logger->addInfo(print_r('-------- BUILDING IMPULSEV2 --------', true));
            $image = $this->_kip->getProductImageHtml(null, $product, true);
            $name = $product->getName();
            $description = $this->_kip->getPresentacion($product, false, [], false, true);
            $this->_logger->addInfo(print_r('PRODUCTID:' . $product->getId(), true));
            try {
                $websiteCode = $this->_storeManager->getWebsite()->getCode();
                $stockId = $this->_stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
                $qtyLeft = $this->_getProductSalableQty->execute($product->getSku(), $stockId);
            } catch (\Exception $e) {
                $qtyLeft = 0;
                $this->_logger->addInfo(print_r('PRODUCTO SIN STOCK IMPULSEV2: ' . $name, true));
            }

            if ($qtyLeft > 0) {
                $result[$product->getId()] = [
                    "p" => $product->getPrice(), //price
                    "m" => $this->_kip->getMinSale($product), //min_qty
                    "i" => $image,
                    "n" => $name,
                    "d" => $description,
                    "s" => $qtyLeft,
                    "u" => $v1 . $v2
                ];
            }

            $this->_logger->addInfo(print_r('-------- END BUILDING IMPULSEV2 --------', true));
        }
        return $result;
    }

    /**
     * @param $response
     */
    private function jsonResponse($response)
    {
        header('Content-Type: application/json');
        if (is_array($response)) {
            echo json_encode($response);
        } else {
            if (is_string($response)) {
                echo $response;
            }
        }
        exit;
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    private function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }

    /**
     * @param $request
     * @return mixed
     */
    private function validatedParams($request)
    {
        if (trim($request['name']) === '') {
            $this->jsonResponse(["status" => 400, "output" => __('Enter the Name and try again.')]);
        }
        if (trim($request['comment']) === '') {
            $this->jsonResponse(["status" => 400, "output" => __('Enter the comment and try again.')]);
        }
        if (false === \strpos($request['email'], '@')) {
            $this->jsonResponse(["status" => 400, "output" => __('The email address is invalid. Verify the email address and try again.')]);
        }

        return $request;
    }

    /**
     * @param $html
     * @param $path
     * @param $banners
     * @return array
     */
    private function extractBanners($html, $path, &$banners)
    {
        /**
         * @var $div \DOMNode
         * @var $child \DOMNode
         */

        $dom = new \DOMDocument();
        try {
            $dom->loadHTML('<?xml encoding="UTF-8">' . $this->_template->filter($html));
        } catch (\Exception $e) {
            return [];
        }
        $divs = $dom->getElementsByTagName('div');
        foreach ($divs as $div) {
            if ($div->getAttribute('class') == 'banner') {
                foreach ($div->childNodes as $child) {
                    $nodes = [$child];
                    foreach ($nodes as $node) {
                        if (method_exists($node, 'getAttribute')) {
                            if (!empty($node->getAttribute('src'))) {
                                $img = explode('?', $node->getAttribute('src'));
                                if (count($img) === 2) {
                                    $key = str_replace('m', '', $img[1]);
                                    $key = str_replace('b', '', $key);
                                    if (!isset($banners[$path][$key])) {
                                        $banners[$path][$key] = [];
                                    }
                                    if (strpos($img[1], 'm') !== false) {
                                        $banners[$path][$key]['mobile'] = $img[0];
                                    } else {
                                        $banners[$path][$key]['desktop'] = $img[0];
                                    }
                                    $banners[$path][$key]['category_id'] = $key;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $html
     * @param $path
     * @param $banners
     * @return array
     */
    private function extractHtmlSeasonSkus($html, &$season)
    {
        $titlesCount = preg_match_all('/ title="[^>]+" show_pager/', $html, $titles);
        $skusCount = preg_match_all('/ conditions_encoded="[^>]+"/', $html, $skus);
        if ($skusCount > 0) {
            foreach ($skus[0] as $i => $skuHtml) {
                $processedSkus = 0;
                $season[$i] = [
                    "title" => isset($titles[0][$i]) ? str_replace('" show_pager', '', str_replace(' title="', '', $titles[0][$i])) : '',
                    "skus" => [],
                ];
                foreach (explode(',', $skuHtml) as $s) {
                    if (strpos($s, '`value`:`') !== false) {
                        if ($processedSkus > 0) {
                            $sku = str_replace('`value`:`', '', $s);
                            $sku = str_replace('`', '', $sku);
                            $sku = str_replace(']', '', $sku);
                            $sku = str_replace('^', '', $sku);
                            $sku = str_replace('"', '', $sku);
                            $season[$i]['skus'][] = $sku;
                        }
                        $processedSkus++;
                    }
                }
            }
        }
        return $season;
    }
}
