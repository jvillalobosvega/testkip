<?php
namespace Bananacode\TaxDocument\Api;

interface TaxDocumentInterface
{
    /**
     * POST for test api
     * @param mixed $data
     * @return mixed[]
     */
    public function add($data);

    /**
     * POST for test api
     * @param mixed $data
     * @return mixed[]
     */
    public function edit($data);

    /**
     * POST for test api
     * @param int $documentId
     * @return mixed[]
     */
    public function delete($documentId);

    /**
     * GET for test api
     * @param int $documentId
     * @return mixed[]
     */
    public function get($documentId);

    /**
     * GET tax categories
     * @return mixed[]
     */
    public function categories();

    /**
     * GET for test api
     * @param int $customerId
     * @param bool $approved
     * @return mixed[]
     */
    public function getDocumentsByCustomer($approved = false);

    /**
     * GET for test api
     * @param int $customerId
     * @return mixed[]
     */
    public function getApprovedDocumentsByCustomer();
}
