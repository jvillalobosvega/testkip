<?php

namespace Bananacode\BacFac\Controller\Checkout;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Form
 * @package Bananacode\BacFac\Controller\Checkout
 * todo: move it to HttpPostActionInterface?
 */
class Form  extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Form constructor.
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        $this->_request = $request;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        $errorMsg = "Ha habido un error procesando tu pago, favor refrescar el sitio e intentar de nuevo.";
        if (isset($params['ReasonCodeDesc'])) {
            if ($params['ReasonCodeDesc'] === 'Transaction is approved.') {
                try {
                    unset($params['MerID']);
                    unset($params['AcqID']);
                    unset($params['Signature']);
                    unset($params['SignatureMethod']);
                } catch (\Exception $e) {}
                $msg = $params['ReasonCodeDesc'];
                $params = json_encode($params);
                echo "<script> window.parent.postMessage({ message: 'notifyAuth', value: { auth: true, msg: '" . $msg . "', data: '" . $params . "'} }, '*'); if(window.ReactNativeWebView){ window.ReactNativeWebView.postMessage(JSON.stringify({ message: 'notifyAuth', value: { auth: true, msg: '" . $msg . "', data: '" . $params . "'}}))}</script>";
                die();
            } else {
                $errorMsg = $params['ReasonCodeDesc'];
                /*if($params['ReasonCodeDesc'] == "Duplicate Order Id not allowed."
                    || ($params['ResponseCode'] == "40" && $params['ReasonCode'] == "3")) {
                    //Order was already charged but something happened...
                }*/
            }
        } else {
            if (isset($params['ReasonCodeDesc'])) {
                $errorMsg = $params['ReasonCodeDesc'];
            }
        }
        echo "<script> window.parent.postMessage({ message: 'notifyAuth', value: { auth: false, msg: '" . $errorMsg . "'} }, '*'); if(window.ReactNativeWebView){ window.ReactNativeWebView.postMessage(JSON.stringify({ message: 'notifyAuth', value: { auth: false, msg: '" . $errorMsg . "'}}))}</script>";
        die();
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
