<?php

namespace Bananacode\ShoppingList\Model;

use Bananacode\ShoppingList\Api\ShoppingListInterface;

class ShoppingListInterfaceModel implements ShoppingListInterface
{
    const PARAMS_TO_VALIDATE_ADD = ["name", "items"];

    const PARAMS_TO_VALIDATE_EDIT = ["name", "items", "list_id"];

    /**
     * @var ShoppingListFactory
     */
    protected $_shoppingList;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    public $_userContext;

    /**
     * ShoppingListInterfaceModel constructor.
     * @param ShoppingListFactory $shoppingList
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Magento\Customer\Model\Session $customer
     */
    public function __construct(
        ShoppingListFactory $shoppingList,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Customer\Model\Session $customer,
        \Magento\Authorization\Model\CompositeUserContext $userContext
    )
    {
        $this->_shoppingList = $shoppingList;
        $this->_customerModel = $customerModel;
        $this->_customer = $customer;
        $this->_userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function add($data)
    {
        if ($customer_id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)){
            try {
                $data = json_decode($data, true);

                if (!$errors = $this->validateParams($data, self::PARAMS_TO_VALIDATE_ADD)) {
                    $this->jsonResponse(["status" => 400, "output" => __('Missing parameters: ' . $errors)]);
                }

                $items = json_encode($data["items"]);
                $name = $data["name"];

                $shoppingList = $this->_shoppingList->create();
                $shoppingList->setData("items", $items);
                $shoppingList->setData("customer_id", $customer_id);
                $shoppingList->setData("name", $name);
                $shoppingList->save();
                $list = explode(",", $shoppingList->getItems());
                $response = ["status" => 200, "output" => $list, "id" => $shoppingList->getId()];
                $this->jsonResponse($response);
            } catch (\Exception $e) {
                $this->jsonResponse(["status" => 400, "output" => __($e->getMessage())]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function edit($data)
    {
        if ($customer_id = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)){
            try {
                $data = json_decode($data, true);

                if (!$errors = $this->validateParams($data, self::PARAMS_TO_VALIDATE_EDIT)) {
                    $this->jsonResponse(["status" => 400, "output" => __('Missing parameters: ' . $errors)]);
                }

                $items = json_encode($data["items"]);
                $list_id = $data["list_id"];
                $name = $data["name"];
                $shoppingList = $this->_shoppingList->create()->load($list_id);
                if ($shoppingList->getId()) {
                    $shoppingList->setData("items", $items);
                    $shoppingList->setData("customer_id", $customer_id);
                    $shoppingList->setData("name", $name);
                    $shoppingList->save();
                    $list = explode(",", $shoppingList->getItems());
                    $response = (["status" => 200, "output" => $list, "id" => $shoppingList->getId()]);
                } else {
                    $response = (["status" => 400, "output" => __('List does not exist.')]);
                }
                $this->jsonResponse($response);
            } catch (\Exception $e) {
                $this->jsonResponse(["status" => 400, "output" => __($e->getMessage())]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($listId)
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)){
            try {
                $shoppingList = $this
                    ->_shoppingList
                    ->create()
                    ->load($listId);
                if ($shoppingList->getId()) {
                    $shoppingList->delete();
                    $response = (["status" => 200, "output" => __('List removed.')]);
                } else {
                    $response = (["status" => 400, "output" => __('List does not exist.')]);
                }
                $this->jsonResponse($response);
            } catch (\Exception $e) {
                $this->jsonResponse(["status" => 400, "output" => __($e->getMessage())]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($listId)
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)){
            try {
                $response = [];
                $shoppingList = $this->_shoppingList->create()->load($listId);
                if ($shoppingList->getId()) {
                    $this->jsonResponse($shoppingList->getData());
                } else {
                    $response = (["status" => 400, "output" => __('List does not exist.')]);
                }
                $this->jsonResponse($response);
            } catch (\Exception $e) {
                $this->jsonResponse(["status" => 400, "output" => __($e->getMessage())]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getListByCustomer()
    {
        if ($customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)){
            try {
                $shoppingLists = $this->_shoppingList->create()->getCollection()->addFieldToFilter('customer_id', $customerId);
                $response = count($shoppingLists->getData()) > 0 ? $shoppingLists->getData() : [];
                $this->jsonResponse($response);
            } catch (\Exception $e) {
                $this->jsonResponse(["status" => 400, "output" => __($e->getMessage())]);
            }
        } else {
            http_response_code(401);
            $this->jsonResponse(["status" => 401, "output" => __('Not allowed.')]);
        }
    }


    /**
     * @param $params
     * @param $array_validate
     * @return bool
     */
    private function validateParams($params, $array_validate)
    {
        $errors = '';
        foreach ($array_validate as $param) {
            if (!isset($params[$param])) {
                $errors .= $param . ' ';
            }
        }

        if(empty($errors)) {
            return true;
        } else {
            return $errors;
        }
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
}
