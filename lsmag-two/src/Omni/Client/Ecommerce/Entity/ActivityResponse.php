<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

class ActivityResponse extends Entity
{

    /**
     * @property string $BookingRef
     */
    protected $BookingRef = null;

    /**
     * @property string $Currency
     */
    protected $Currency = null;

    /**
     * @property string $ErrorString
     */
    protected $ErrorString = null;

    /**
     * @property string $ItemNo
     */
    protected $ItemNo = null;

    /**
     * @property float $LineDiscount
     */
    protected $LineDiscount = null;

    /**
     * @property float $Quantity
     */
    protected $Quantity = null;

    /**
     * @property string $ReservationNo
     */
    protected $ReservationNo = null;

    /**
     * @property float $TotalAmount
     */
    protected $TotalAmount = null;

    /**
     * @property float $UnitPrice
     */
    protected $UnitPrice = null;

    /**
     * @param string $BookingRef
     * @return $this
     */
    public function setBookingRef($BookingRef)
    {
        $this->BookingRef = $BookingRef;
        return $this;
    }

    /**
     * @return string
     */
    public function getBookingRef()
    {
        return $this->BookingRef;
    }

    /**
     * @param string $Currency
     * @return $this
     */
    public function setCurrency($Currency)
    {
        $this->Currency = $Currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->Currency;
    }

    /**
     * @param string $ErrorString
     * @return $this
     */
    public function setErrorString($ErrorString)
    {
        $this->ErrorString = $ErrorString;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorString()
    {
        return $this->ErrorString;
    }

    /**
     * @param string $ItemNo
     * @return $this
     */
    public function setItemNo($ItemNo)
    {
        $this->ItemNo = $ItemNo;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemNo()
    {
        return $this->ItemNo;
    }

    /**
     * @param float $LineDiscount
     * @return $this
     */
    public function setLineDiscount($LineDiscount)
    {
        $this->LineDiscount = $LineDiscount;
        return $this;
    }

    /**
     * @return float
     */
    public function getLineDiscount()
    {
        return $this->LineDiscount;
    }

    /**
     * @param float $Quantity
     * @return $this
     */
    public function setQuantity($Quantity)
    {
        $this->Quantity = $Quantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->Quantity;
    }

    /**
     * @param string $ReservationNo
     * @return $this
     */
    public function setReservationNo($ReservationNo)
    {
        $this->ReservationNo = $ReservationNo;
        return $this;
    }

    /**
     * @return string
     */
    public function getReservationNo()
    {
        return $this->ReservationNo;
    }

    /**
     * @param float $TotalAmount
     * @return $this
     */
    public function setTotalAmount($TotalAmount)
    {
        $this->TotalAmount = $TotalAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->TotalAmount;
    }

    /**
     * @param float $UnitPrice
     * @return $this
     */
    public function setUnitPrice($UnitPrice)
    {
        $this->UnitPrice = $UnitPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->UnitPrice;
    }


}
