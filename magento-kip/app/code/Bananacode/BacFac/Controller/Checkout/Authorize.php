<?php

namespace Bananacode\BacFac\Controller\Checkout;

use Bananacode\BacFac\Model\Ui\ConfigProvider as BacFacProvider;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Authorize
 * @package Bananacode\BacFac\Controller\Checkout
 */
class Authorize extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Bananacode\BacFac\Helper\Data
     */
    protected $_bananaHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Bananacode\BacFac\Helper\Encryption
     */
    private $_bananaCryptor;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    public $_userContext;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * Authorize constructor.
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Bananacode\BacFac\Helper\Data $bananaHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bananacode\BacFac\Helper\Encryption $bananaCryptor
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Magento\Customer\Model\Session $customer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Bananacode\BacFac\Helper\Data $bananaHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Bananacode\BacFac\Helper\Encryption $bananaCryptor,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Customer\Model\Session $customer
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_bananaHelper = $bananaHelper;
        $this->_request = $request;
        $this->_bananaCryptor = $bananaCryptor;
        $this->_userContext = $userContext;
        $this->_customer = $customer;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if(!$customerId = $this->authUser()) {
            http_response_code(401);
            //$error = ["status" => 401, "output" => __('What in hell are you doing? Get away from here right now before I call the police.')];
            $error = ["status" => 401, "output" => __('Tu sesión ha expirado, por favor inicia sesión.')];
            header('Content-Type: application/json');
            echo $error ? json_encode($error) : $error;
            exit;
        }

        $params = $this->_request->getParams();
        $this->_bananaHelper->_config->setMethodCode(BacFacProvider::CODE);
        $ekey = str_split(
            $this->_bananaHelper->_config->getValue('acquirer_id') .
            $this->_bananaHelper->_config->getValue('merchant_id'),
            3);
        $card_details = (array)json_decode($this->_bananaCryptor->decrypt($params["nonce"], $ekey[0] . $ekey[1] . $ekey[2]));
        $quote = $this->_checkoutSession->getQuote();
        $quote->reserveOrderId();
        $result_soap = $this->_bananaHelper->generate_request(
            $card_details,
            $quote->getReservedOrderId(),
            null,
            true,
            $customerId
        );

        $Authorize3DSResult = $result_soap->Authorize3DSResult;
        if ($Authorize3DSResult->ResponseCode == "0" && $Authorize3DSResult->ResponseCodeDescription == "Success"
        ) {
            echo $Authorize3DSResult->HTMLFormData;
        } else {
            echo $Authorize3DSResult->ResponseCodeDescription;
        }

        die();
    }

    private function authUser() {
        if(!$id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {

            if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $token = $_SERVER['HTTP_AUTHORIZATION'];
            } else {
                if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                    $token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                } else {
                    return false;
                }
            }

            $url = "http://";
            if(isset($_SERVER['HTTPS'])) {
                if($_SERVER['HTTPS'] === 'on') {
                    $url = "https://";
                }
            }
            $url .= $_SERVER['HTTP_HOST'];

            $ch = curl_init($url. "/rest/V1/customers/me");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Authorization: " . $token)
            );

            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //FOR DEV ONLY
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //FOR DEV ONLY

            $result = curl_exec($ch);
            curl_close($ch);

            if($result) {
                $customer = json_decode(trim($result), true);
                $id = isset($customer['id']) ? $customer['id'] : false;
            } else {
                return false;
            }
        }
        return $id;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * Disable Magento's CSRF validation.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
