<?php

namespace Bananacode\Kip\Plugin;

use Magento\Framework\Message\MessageInterface;

/**
 * Class LoginPost
 * @package Bananacode\Kip\Plugin
 */
class LoginPost
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * LoginPost constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Change redirect after signup to home instead of dashboard.
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     * @return mixed
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        \Magento\Framework\Controller\Result\Redirect $result)
    {
        /**
         * @var MessageInterface $message
         */
        $success = true;
        foreach ($this->messageManager->getMessages() as $message) {
            if(
                $message->getType() == 'error' ||
                $message->getType() == 'warning' ||
                $message->getType() == 'notice'
            ) {
                $success = false;
            }
        }

        //Redirect HP
        if($success) {
            $result->setPath('/', ['?kipcart=true']);
        }

        return $result;
    }
}
