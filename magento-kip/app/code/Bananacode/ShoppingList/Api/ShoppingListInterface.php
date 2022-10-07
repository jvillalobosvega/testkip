<?php
namespace Bananacode\ShoppingList\Api;

interface ShoppingListInterface
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
     * @param int $listId
     * @return mixed[]
     */
    public function delete($listId);

    /**
     * GET for test api
     * @param int $listId
     * @return mixed[]
     */
    public function get($listId);

    /**
     * GET for test api
     * @return mixed[]
     */
    public function getListByCustomer();
}
