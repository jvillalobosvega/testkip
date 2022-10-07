<?php

namespace Bananacode\TaxDocument\Model;

use Bananacode\TaxDocument\Api\TaxDocumentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**(
 * Class TaxDocumentInterfaceModel
 * @package Bananacode\TaxDocument\Model
 */
class TaxDocumentInterfaceModel implements TaxDocumentInterface
{
    const FOLDER = 'taxdf2t245d/';

    const PARAMS_TO_VALIDATE_ADD_CREDIT = [
        "customer_name",
        "tax_identification_number",
        "registry_number",
        "economic_activity",
        "head_office_address",
        "category_id",
        "document_image"
    ];

    const PARAMS_TO_VALIDATE_ADD_IVA = [
        "customer_name",
        "expiration_date",
        "id_number",
        "entity",
        "document_image"
    ];

    const PARAMS_TO_VALIDATE_EDIT_CREDIT = [
        "customer_name",
        "tax_identification_number",
        "registry_number",
        "economic_activity",
        "head_office_address",
        "category_id",
        "document_id"
    ];

    const PARAMS_TO_VALIDATE_EDIT_IVA = [
        "customer_name",
        "expiration_date",
        "id_number",
        "entity",
        "document_id"
    ];

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @var TaxDocumentFactory
     */
    protected $_taxDocument;

    /**
     * @var TaxCategoryFactory
     */
    protected $_categoryTaxModel;

    /**
     * @var ResourceModel\TaxCategory\Collection
     */
    protected $_categoryTaxCollection;

    /**
     * @var TaxDocumentTypeFactory
     */
    protected $_taxDocumentType;

    /**
     * @var ResourceModel\TaxDocumentType
     */
    protected $_taxDocumentTypeResource;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    public $_userContext;

    /**
     * @var ResourceModel\TaxDocument\Collection
     */
    public $_taxDocumentCollection;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    private $_adminContext;

    /**
     * @param TaxDocumentFactory $taxDocument
     * @param ResourceModel\TaxDocument\Collection $taxDocumentCollection
     * @param TaxCategoryFactory $categoryTax
     * @param TaxDocumentType $taxDocumentType
     * @param ResourceModel\TaxDocumentType $taxDocumentTypeResource
     * @param ResourceModel\TaxCategory\Collection $categoryTaxCollection
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Magento\Backend\App\Action\Context $adminContext
     */
    public function __construct(
        TaxDocumentFactory $taxDocument,
        \Bananacode\TaxDocument\Model\ResourceModel\TaxDocument\Collection $taxDocumentCollection,
        TaxCategoryFactory $categoryTax,
        TaxDocumentType $taxDocumentType,
        ResourceModel\TaxDocumentType $taxDocumentTypeResource,
        ResourceModel\TaxCategory\Collection $categoryTaxCollection,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customer,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Backend\App\Action\Context $adminContext
    )
    {
        $this->_taxDocument = $taxDocument;
        $this->_taxDocumentCollection = $taxDocumentCollection;

        $this->_categoryTaxModel = $categoryTax;
        $this->_categoryTaxCollection = $categoryTaxCollection;

        $this->_taxDocumentType = $taxDocumentType;
        $this->_taxDocumentTypeResource = $taxDocumentTypeResource;

        $this->_fileSystem = $fileSystem;
        $this->_storeManager = $storeManager;

        $this->_customer = $customer;
        $this->_userContext = $userContext;
        $this->_customerModel = $customerModel;

        $this->_adminContext = $adminContext;
    }

    /**
     * {@inheritdoc}
     */
    public function add($data)
    {
        if ($customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                $data = json_decode($data, true);
                $data['customer_id'] = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);

                if (!isset($data["document_type"])) {
                    $response = (["status" => 400, "output" => __('Document type has to be specified.')]);
                    $this->jsonResponse($response);
                }

                $document_type = $data["document_type"];
                $this->_taxDocumentTypeResource
                    ->load(
                        $this->_taxDocumentType,
                        $document_type,
                        'name'
                    );
                if (!$this->_taxDocumentType->getId()) {
                    $response = (["status" => 400, "output" => __('The document type is incorrect.')]);
                    $this->jsonResponse($response);
                }

                $limit = $this->_taxDocumentCollection
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('document_type', $this->_taxDocumentType->getId())
                    ->load()
                    ->count();
                if($limit > 0) {
                    $this->jsonResponse(["status" => 400, "output" => __('Solo puedes agregar un documento fiscal por tipo.')]);
                }

                $validate_array = ($document_type == 'CCF') ? self::PARAMS_TO_VALIDATE_ADD_CREDIT : self::PARAMS_TO_VALIDATE_ADD_IVA;
                if (!$errors = $this->validateParams($data, $validate_array)) {
                    $response = (["status" => 400, "output" => __('Missing parameters: ' . $errors)]);
                    $this->jsonResponse($response);
                }

                if ($document_type == 'CCF') {
                    $category_id = $data["category_id"];
                    $category_tax_check = $this->_categoryTaxModel->create()->load($category_id);
                    if (!$category_tax_check->getId()) {
                        $response = (["status" => 400, "output" => __('Category does not exist.')]);
                        $this->jsonResponse($response);
                    }
                }

                try {
                    if (isset($data["document_image"])) {
                        $images = [];
                        foreach ($data["document_image"] as $i => $image) {
                            $varDir = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                            $extension = '.' . explode('/', mime_content_type($image))[1];
                            list($type, $image) = explode(';', $image);
                            list(, $image) = explode(',', $image);
                            $image = base64_decode($image);
                            $file_name = time() . $data["customer_id"] . $i;
                            $filePath = $varDir . self::FOLDER . $file_name . $extension;
                            file_put_contents($filePath, $image);
                            $currentStore = $this->_storeManager->getStore();
                            $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                            $images[] = $mediaUrl . self::FOLDER . $file_name . $extension;
                        }
                        $data["document_image"] = json_encode($images);
                    }

                    $taxDocument = $this->_taxDocument->create();
                    $data['document_type'] = $this->_taxDocumentType->getId();
                    $taxDocument->setData($data);
                    $taxDocument->save();
                    $response = (["status" => 200, "output" => $taxDocument->getDocumentId()]);
                } catch (\Exception $e) {
                    $response = (["status" => 400, "output" => __($e->getMessage())]);
                }
            } catch (\Exception $e) {
                $response = (["status" => 400, "output" => __($e->getMessage())]);
            }

            $this->jsonResponse($response);
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
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                $data = json_decode($data, true);
                $document_type = $data["document_type"];
                $document_id = $data["document_id"];
                if (!isset($data["document_type"])) {
                    $response = (["status" => 400, "output" => __('Document type has to be specified.')]);
                    $this->jsonResponse($response);
                }

                $this->_taxDocumentTypeResource->load(
                    $this->_taxDocumentType,
                    $document_type,
                    'name'
                );
                if (!$this->_taxDocumentType->getId()) {
                    $response = (["status" => 400, "output" => __('The document type is incorrect.')]);
                    $this->jsonResponse($response);
                }

                $validate_array = ($document_type == 'CCF') ? self::PARAMS_TO_VALIDATE_EDIT_CREDIT : self::PARAMS_TO_VALIDATE_EDIT_IVA;
                if (!$errors = $this->validateParams($data, $validate_array)) {
                    $response = (["status" => 400, "output" => __('Missing parameters: ' . $errors)]);
                    $this->jsonResponse($response);
                }

                if ($document_type == 'CCF') {
                    $category_id = $data["category_id"];
                    $category_tax_check = $this->_categoryTaxModel->create()->load($category_id);
                    if (!$category_tax_check->getId()) {
                        $response = (["status" => 400, "output" => __('Category does not exist.')]);
                        $this->jsonResponse($response);
                    }
                }

                $taxDocument = $this->_taxDocument->create()->load($document_id);
                if ($taxDocument->getDocumentId()) {
                    $currentImages = json_decode($taxDocument->getData('document_image'), true);
                    if (isset($data["document_image"])) {
                        foreach ($data["document_image"] as $i => $image) {
                            if ($image) {
                                $varDir = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                                $extension = '.' . explode('/', mime_content_type($image))[1];
                                list($type, $image) = explode(';', $image);
                                list(, $image) = explode(',', $image);
                                $image = base64_decode($image);
                                $file_name = time() . $data["customer_id"] . $i;
                                $filePath = $varDir . self::FOLDER . $file_name . $extension;
                                file_put_contents($filePath, $image);
                                $currentStore = $this->_storeManager->getStore();
                                $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                                $currentImages[$i] = $mediaUrl . self::FOLDER . $file_name . $extension;
                            }
                        }
                    }
                    $data["document_image"] = json_encode($currentImages);
                    $data['document_type'] = $this->_taxDocumentType->getId();
                    $taxDocument->setData($data);
                    $taxDocument->save();
                    $response = (["status" => 200, "output" => $taxDocument->getData()]);
                } else {
                    $response = (["status" => 400, "output" => __('The document does not exist.')]);
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
    public function delete($documentId)
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                $taxDocument = $this->_taxDocument->create()->load($documentId);

                if ($taxDocument->getId()) {
                    $taxDocument->delete();
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
    public function get($documentId)
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                $taxDocument = $this->_taxDocument->create()->load($documentId);

                if ($taxDocument->getId()) {
                    $response = (["status" => 200, "output" => $taxDocument->getData()]);
                } else {
                    $response = (["status" => 400, "output" => __('This document does not exist.')]);
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
    public function categories()
    {
        if ($this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false)) {
            try {
                $this->jsonResponse(["status" => 200, "output" => $this->_categoryTaxCollection->load()->toArray()]);
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
    public function getDocumentsByCustomer($approved = false)
    {
        $adminRequest = $this->_adminContext->getSession()->getSessionId() === $this->_adminContext->getRequest()->getParam('form_key');
        $frontendUser = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);
        if ($frontendUser || $adminRequest) {

            if($adminRequest) {
                $customerId = $this->_adminContext->getRequest()->getParam('ci');
            }  else {
                $customerId = $frontendUser;
            }

            try {
                $taxDocuments =
                    $this->_taxDocumentCollection
                        ->addFieldToFilter('customer_id', $customerId);

                if ($approved) {
                    $taxDocuments->addFieldToFilter('status', 2);
                }

                $taxDocuments->join(
                    [
                        'ty' => 'bananacode_taxdocumenttype'],
                    'document_type = ty.entity_id',
                    'name'
                );

                $this->jsonResponse(["status" => 200, "output" => $taxDocuments->toArray()]);
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
    public function getApprovedDocumentsByCustomer()
    {
        $this->getDocumentsByCustomer(true);
    }


    /**
     * @param $params
     * @param $array_validate
     * @return bool|string
     */
    private function validateParams($params, $array_validate)
    {
        $errors = '';
        foreach ($array_validate as $param) {
            if (!isset($params[$param])) {
                $errors .= $param . ' ';
            }
        }

        if (empty($errors)) {
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
        echo $response ? json_encode($response) : $response;
        exit;
    }
}
