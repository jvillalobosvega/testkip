<?php

namespace Bananacode\Kip\Api;

interface KipApiInterface
{
    /**
     * Report site issue
     *
     * @param string $data
     * @return mixed
     */
    public function report($data);

    /**
     * Update customer avatar
     *
     * @param string $base64
     * @param string $id
     * @return mixed
     */
    public function avatar($base64, $id);

    /**
     * Update customer experience
     *
     * @param string $data
     * @return mixed
     */
    public function experience($data);

    /**
     * Verify if product is on customer wishlist
     *
     * @param integer $product_id
     * @return mixed
     */
    public function isWished($product_id);

    /**
     * Get current customer wish lists
     *
     * @return mixed
     */
    public function wishLists();

    /**
     * Add/Delete product to customer wishlist
     *
     * @param integer $product_id
     * @param string $name
     * @return mixed
     */
    public function addToWishList($product_id, $name);

    /**
     * Delete product customer wishlist
     *
     * @param string $name
     * @return mixed
     */
    public function deleteWishList($name);

    /**
     * Get current customer logged in
     *
     * @return mixed
     */
    public function currentCustomer();

    /**
     * Get current customer logged in conctact data
     *
     * @return mixed
     */
    public function currentCustomerContactData();

    /**
     * Get current customer order summary
     *
     * @return mixed
     */
    public function orderSummary();

    /**
     * Get current customer recurring products
     *
     * @return mixed
     */
    public function recurring();

    /**
     * Return main categories tree
     *
     * @return mixed
     */
    public function categories();

    /**
     * Get available offers
     *
     * @return mixed
     */
    public function offers();

    /**
     * Get dynamic banners
     *
     * @return mixed
     */
    public function banners();

    /**
     * Get season skus
     *
     * @return mixed
     */
    public function season();

    /**
     * Update quote cart
     * @param string $data
     * @return mixed
     */
    public function kipcart($data);


    /**
     * GET Impulse v1
     * @param string $data
     * @return mixed
     */
    public function kipimpulsev1();


    /**
     * GET Impulse v2
     * @param string $data
     * @return mixed
     */
    public function kipimpulsev2($data);


     /**
     * POST CLEAR CART
     * @param string $data
     * @return mixed
     */
    public function cleanCart($data);

    
    /**
     * Update LS Retail cart
     * @param string $data
     * @return mixed
     */
    public function lscart($data);

    /**
     * Get logged in customer token
     * @return mixed
     */
    public function token();
}
