<?php

namespace Bananacode\Kip\Observer;

use Exception;
use Magento\Customer\Model\Session\Proxy;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerRegisterPreDispatchObserver
 * @package Bananacode\Kip\Observer
 */
class CustomerRegisterPreDispatchObserver implements ObserverInterface
{
    /** @var ManagerInterface */
    private $messageManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var Proxy */
    private $customerSession;

    /** @var RedirectInterface */
    private $redirectInterface;

    /** @var ActionFlag */
    private $actionFlag;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * CustomerRegisterPreDispatchObserver constructor.
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param Proxy $customerSession
     * @param RedirectInterface $redirectInterface
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        Proxy $customerSession,
        RedirectInterface $redirectInterface,
        ActionFlag $actionFlag,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->messageManager    = $messageManager;
        $this->logger            = $logger;
        $this->customerSession   = $customerSession;
        $this->redirectInterface = $redirectInterface;
        $this->actionFlag        = $actionFlag;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $parameters = $observer->getRequest()->getParams();
        $isNotValid = false;
        if (!empty($parameters['email'])) {
            if($emails = $this->getConfig('bananacode/beta/emails')) {
                if(!empty(trim($emails))) {
                    $emails = explode(',', $emails);
                    if(!in_array($parameters['email'], $emails)) {
                        $this->messageManager->addErrorMessage(__('Estamos en pruebas BETA. Registros controlados únicamente.'));
                        $isNotValid = true;
                    }
                }
            }
        }

        if ($isNotValid) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $observer->getControllerAction()
                ->getResponse()->setRedirect($this->redirectInterface->getRefererUrl());
            $this->customerSession->setCustomerFormData($parameters);
        }

        return $this;
    }

    /**
     * @param $config
     * @return mixed
     */
    private function getConfig($config)
    {
        return $this->_scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
