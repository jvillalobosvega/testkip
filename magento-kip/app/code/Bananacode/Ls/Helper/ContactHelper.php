<?php

namespace Bananacode\Ls\Helper;

use Exception;
use \Ls\Core\Model\LSR;
use \Ls\Omni\Client\Ecommerce\Entity;
use Ls\Omni\Client\Ecommerce\Entity\MemberContact;
use \Ls\Omni\Client\Ecommerce\Operation;
use \Ls\Omni\Client\ResponseInterface;
use \Ls\Omni\Model\Cache\Type;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Class ContactHelper
 * @package Bananacode\Ls\Helper
 */
class ContactHelper extends \Ls\Omni\Helper\ContactHelper
{
    /**
     * @param $id
     * @return string
     */
    public function getGenderStringById($id)
    {
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();

            /**
             * @var $eavConfig \Magento\Eav\Model\Config
             */
            $eavConfig = $om->create('Magento\Eav\Model\Config');
            $attribute = $eavConfig->getAttribute('customer', 'gender');
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option) {
                if($option['value'] == $id) {
                    switch ($option['label']) {
                        case 'Male':
                            return Entity\Enum\Gender::MALE;
                            break;
                        case 'Female':
                            return Entity\Enum\Gender::FEMALE;
                            break;
                    }
                }
            }
        } catch (\Exception $e) {

        }
        return  Entity\Enum\Gender::UNKNOWN;
    }

    /**
     * @param MemberContact $contact
     * @param Customer $customer
     * @return mixed
     * @throws InputException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setCustomerAttributesValues($contact, $customer)
    {
        $customer->setData('lsr_id', $contact->getId());
        if (!empty($contact->getBirthDay()) && $contact->getBirthDay() != '1753-01-01T00:00:00') {
            $customer->setData('dob', $this->date->date("Y-m-d", strtotime($contact->getBirthDay())));
        }
        if (!empty($contact->getGender())) {
            //$genderValue = ($contact->getGender() == Entity\Enum\Gender::MALE) ? 1 : (($contact->getGender() == Entity\Enum\Gender::FEMALE) ? 2 : '');
            //$customer->setData('gender', $genderValue);
        }
        if (!empty($contact->getUserName())) {
            $customer->setData('lsr_username', $contact->getUserName());
        }
        if (!empty($contact->getLoggedOnToDevice()) &&
            !empty($contact->getLoggedOnToDevice()->getSecurityToken())) {
            $token = $contact->getLoggedOnToDevice()->getSecurityToken();
            $customer->setData('lsr_token', $token);
        }
        if (!empty($contact->getCards()) &&
            !empty($contact->getCards()->getCard()[0]) &&
            !empty($contact->getCards()->getCard()[0]->getId())) {
            $customer->setData('lsr_cardid', $contact->getCards()->getCard()[0]->getId());
        }
        if (!empty($contact->getAccount()) &&
            !empty($contact->getAccount()->getScheme()) &&
            !empty($contact->getAccount()->getScheme()->getId())) {
            $customerGroupId = $this->getCustomerGroupIdByName(
                $contact->getAccount()->getScheme()->getId()
            );
            $customer->setGroupId($customerGroupId);
            $this->customerSession->setCustomerGroupId($customerGroupId);
        }
        return $customer;
    }
}
