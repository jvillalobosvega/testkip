<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

use Ls\Omni\Client\Ecommerce\Entity\Enum\NotificationTextType;
use Ls\Omni\Client\Ecommerce\Entity\Enum\NotificationStatus;
use Ls\Omni\Exception\InvalidEnumException;

class Notification extends Entity
{

    /**
     * @property ArrayOfImageView $Images
     */
    protected $Images = null;

    /**
     * @property string $ContactId
     */
    protected $ContactId = null;

    /**
     * @property string $Created
     */
    protected $Created = null;

    /**
     * @property string $Description
     */
    protected $Description = null;

    /**
     * @property string $Details
     */
    protected $Details = null;

    /**
     * @property string $ExpiryDate
     */
    protected $ExpiryDate = null;

    /**
     * @property NotificationTextType $NotificationTextType
     */
    protected $NotificationTextType = null;

    /**
     * @property string $QRText
     */
    protected $QRText = null;

    /**
     * @property NotificationStatus $Status
     */
    protected $Status = null;

    /**
     * @param ArrayOfImageView $Images
     * @return $this
     */
    public function setImages($Images)
    {
        $this->Images = $Images;
        return $this;
    }

    /**
     * @return ArrayOfImageView
     */
    public function getImages()
    {
        return $this->Images;
    }

    /**
     * @param string $ContactId
     * @return $this
     */
    public function setContactId($ContactId)
    {
        $this->ContactId = $ContactId;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactId()
    {
        return $this->ContactId;
    }

    /**
     * @param string $Created
     * @return $this
     */
    public function setCreated($Created)
    {
        $this->Created = $Created;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->Created;
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
     * @param string $Details
     * @return $this
     */
    public function setDetails($Details)
    {
        $this->Details = $Details;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->Details;
    }

    /**
     * @param string $ExpiryDate
     * @return $this
     */
    public function setExpiryDate($ExpiryDate)
    {
        $this->ExpiryDate = $ExpiryDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiryDate()
    {
        return $this->ExpiryDate;
    }

    /**
     * @param NotificationTextType|string $NotificationTextType
     * @return $this
     * @throws InvalidEnumException
     */
    public function setNotificationTextType($NotificationTextType)
    {
        if ( ! $NotificationTextType instanceof NotificationTextType ) {
            if ( NotificationTextType::isValid( $NotificationTextType ) )
                $NotificationTextType = new NotificationTextType( $NotificationTextType );
            elseif ( NotificationTextType::isValidKey( $NotificationTextType ) )
                $NotificationTextType = new NotificationTextType( constant( "NotificationTextType::$NotificationTextType" ) );
            elseif ( ! $NotificationTextType instanceof NotificationTextType )
                throw new InvalidEnumException();
        }
        $this->NotificationTextType = $NotificationTextType->getValue();

        return $this;
    }

    /**
     * @return NotificationTextType
     */
    public function getNotificationTextType()
    {
        return $this->NotificationTextType;
    }

    /**
     * @param string $QRText
     * @return $this
     */
    public function setQRText($QRText)
    {
        $this->QRText = $QRText;
        return $this;
    }

    /**
     * @return string
     */
    public function getQRText()
    {
        return $this->QRText;
    }

    /**
     * @param NotificationStatus|string $Status
     * @return $this
     * @throws InvalidEnumException
     */
    public function setStatus($Status)
    {
        if ( ! $Status instanceof NotificationStatus ) {
            if ( NotificationStatus::isValid( $Status ) )
                $Status = new NotificationStatus( $Status );
            elseif ( NotificationStatus::isValidKey( $Status ) )
                $Status = new NotificationStatus( constant( "NotificationStatus::$Status" ) );
            elseif ( ! $Status instanceof NotificationStatus )
                throw new InvalidEnumException();
        }
        $this->Status = $Status->getValue();

        return $this;
    }

    /**
     * @return NotificationStatus
     */
    public function getStatus()
    {
        return $this->Status;
    }


}
