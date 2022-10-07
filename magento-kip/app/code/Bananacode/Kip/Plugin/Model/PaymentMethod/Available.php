<?php

namespace Bananacode\Kip\Plugin\Model\PaymentMethod;

class Available
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $_state;

    /**
     * CreatePost constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\State $state
    )
    {
        $this->_state = $state;
    }

    /**
     * @param \Magento\Payment\Model\Method\AbstractMethod $subject
     * @param $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterIsAvailable(
        \Magento\Payment\Model\Method\AbstractMethod $subject,
        $result
    ) {
        if($subject->getCode() == 'checkmo' && $this->_state->getAreaCode() != 'adminhtml') {
            return false;
        }

        if($subject->getCode() == 'free') {
            return true;
        }

        return $result;
    }
}
