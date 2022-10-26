<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

use Ls\Omni\Client\Ecommerce\Entity\Enum\AdvertisementType;
use Ls\Omni\Exception\InvalidEnumException;

class Advertisement
{

    /**
     * @property ArrayOfstring $MenuIds
     */
    protected $MenuIds = null;

    /**
     * @property AdvertisementType $AdType
     */
    protected $AdType = null;

    /**
     * @property string $AdValue
     */
    protected $AdValue = null;

    /**
     * @property string $Description
     */
    protected $Description = null;

    /**
     * @property string $ExpirationDate
     */
    protected $ExpirationDate = null;

    /**
     * @property string $Id
     */
    protected $Id = null;

    /**
     * @property ImageView $ImageView
     */
    protected $ImageView = null;

    /**
     * @property int $RV
     */
    protected $RV = null;

    /**
     * @param ArrayOfstring $MenuIds
     * @return $this
     */
    public function setMenuIds($MenuIds)
    {
        $this->MenuIds = $MenuIds;
        return $this;
    }

    /**
     * @return ArrayOfstring
     */
    public function getMenuIds()
    {
        return $this->MenuIds;
    }

    /**
     * @param AdvertisementType|string $AdType
     * @return $this
     * @throws InvalidEnumException
     */
    public function setAdType($AdType)
    {
        if ( ! $AdType instanceof AdvertisementType ) {
            if ( AdvertisementType::isValid( $AdType ) )
                $AdType = new AdvertisementType( $AdType );
            elseif ( AdvertisementType::isValidKey( $AdType ) )
                $AdType = new AdvertisementType( constant( "AdvertisementType::$AdType" ) );
            elseif ( ! $AdType instanceof AdvertisementType )
                throw new InvalidEnumException();
        }
        $this->AdType = $AdType->getValue();

        return $this;
    }

    /**
     * @return AdvertisementType
     */
    public function getAdType()
    {
        return $this->AdType;
    }

    /**
     * @param string $AdValue
     * @return $this
     */
    public function setAdValue($AdValue)
    {
        $this->AdValue = $AdValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdValue()
    {
        return $this->AdValue;
    }

    /**
     * @param string $Description
     * @return $this
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $ExpirationDate
     * @return $this
     */
    public function setExpirationDate($ExpirationDate)
    {
        $this->ExpirationDate = $ExpirationDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationDate()
    {
        return $this->ExpirationDate;
    }

    /**
     * @param string $Id
     * @return $this
     */
    public function setId($Id)
    {
        $this->Id = $Id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * @param ImageView $ImageView
     * @return $this
     */
    public function setImageView($ImageView)
    {
        $this->ImageView = $ImageView;
        return $this;
    }

    /**
     * @return ImageView
     */
    public function getImageView()
    {
        return $this->ImageView;
    }

    /**
     * @param int $RV
     * @return $this
     */
    public function setRV($RV)
    {
        $this->RV = $RV;
        return $this;
    }

    /**
     * @return int
     */
    public function getRV()
    {
        return $this->RV;
    }


}
