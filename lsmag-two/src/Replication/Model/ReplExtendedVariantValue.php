<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Replication\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Ls\Replication\Api\Data\ReplExtendedVariantValueInterface;

class ReplExtendedVariantValue extends AbstractModel implements ReplExtendedVariantValueInterface, IdentityInterface
{

    public const CACHE_TAG = 'ls_replication_repl_extended_variant_value';

    protected $_cacheTag = 'ls_replication_repl_extended_variant_value';

    protected $_eventPrefix = 'ls_replication_repl_extended_variant_value';

    /**
     * @property string $Code
     */
    protected $Code = null;

    /**
     * @property int $DimensionLogicalOrder
     */
    protected $DimensionLogicalOrder = null;

    /**
     * @property string $Dimensions
     */
    protected $Dimensions = null;

    /**
     * @property string $FrameworkCode
     */
    protected $FrameworkCode = null;

    /**
     * @property boolean $IsDeleted
     */
    protected $IsDeleted = null;

    /**
     * @property string $ItemId
     */
    protected $ItemId = null;

    /**
     * @property int $LogicalOrder
     */
    protected $LogicalOrder = null;

    /**
     * @property string $Timestamp
     */
    protected $Timestamp = null;

    /**
     * @property string $Value
     */
    protected $Value = null;

    /**
     * @property string $scope
     */
    protected $scope = null;

    /**
     * @property int $scope_id
     */
    protected $scope_id = null;

    /**
     * @property boolean $processed
     */
    protected $processed = null;

    /**
     * @property boolean $is_updated
     */
    protected $is_updated = null;

    /**
     * @property boolean $is_failed
     */
    protected $is_failed = null;

    /**
     * @property string $created_at
     */
    protected $created_at = null;

    /**
     * @property string $updated_at
     */
    protected $updated_at = null;

    /**
     * @property string $checksum
     */
    protected $checksum = null;

    /**
     * @property string $processed_at
     */
    protected $processed_at = null;

    public function _construct()
    {
        $this->_init( 'Ls\Replication\Model\ResourceModel\ReplExtendedVariantValue' );
    }

    public function getIdentities()
    {
        return [ self::CACHE_TAG . '_' . $this->getId() ];
    }

    /**
     * @param string $Code
     * @return $this
     */
    public function setCode($Code)
    {
        $this->setData( 'Code', $Code );
        $this->Code = $Code;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData( 'Code' );
    }

    /**
     * @param int $DimensionLogicalOrder
     * @return $this
     */
    public function setDimensionLogicalOrder($DimensionLogicalOrder)
    {
        $this->setData( 'DimensionLogicalOrder', $DimensionLogicalOrder );
        $this->DimensionLogicalOrder = $DimensionLogicalOrder;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getDimensionLogicalOrder()
    {
        return $this->getData( 'DimensionLogicalOrder' );
    }

    /**
     * @param string $Dimensions
     * @return $this
     */
    public function setDimensions($Dimensions)
    {
        $this->setData( 'Dimensions', $Dimensions );
        $this->Dimensions = $Dimensions;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getDimensions()
    {
        return $this->getData( 'Dimensions' );
    }

    /**
     * @param string $FrameworkCode
     * @return $this
     */
    public function setFrameworkCode($FrameworkCode)
    {
        $this->setData( 'FrameworkCode', $FrameworkCode );
        $this->FrameworkCode = $FrameworkCode;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getFrameworkCode()
    {
        return $this->getData( 'FrameworkCode' );
    }

    /**
     * @param boolean $IsDeleted
     * @return $this
     */
    public function setIsDeleted($IsDeleted)
    {
        $this->setData( 'IsDeleted', $IsDeleted );
        $this->IsDeleted = $IsDeleted;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->getData( 'IsDeleted' );
    }

    /**
     * @param string $ItemId
     * @return $this
     */
    public function setItemId($ItemId)
    {
        $this->setData( 'ItemId', $ItemId );
        $this->ItemId = $ItemId;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->getData( 'ItemId' );
    }

    /**
     * @param int $LogicalOrder
     * @return $this
     */
    public function setLogicalOrder($LogicalOrder)
    {
        $this->setData( 'LogicalOrder', $LogicalOrder );
        $this->LogicalOrder = $LogicalOrder;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getLogicalOrder()
    {
        return $this->getData( 'LogicalOrder' );
    }

    /**
     * @param string $Timestamp
     * @return $this
     */
    public function setTimestamp($Timestamp)
    {
        $this->setData( 'Timestamp', $Timestamp );
        $this->Timestamp = $Timestamp;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->getData( 'Timestamp' );
    }

    /**
     * @param string $Value
     * @return $this
     */
    public function setValue($Value)
    {
        $this->setData( 'Value', $Value );
        $this->Value = $Value;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getData( 'Value' );
    }

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->setData( 'scope', $scope );
        $this->scope = $scope;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->getData( 'scope' );
    }

    /**
     * @param int $scope_id
     * @return $this
     */
    public function setScopeId($scope_id)
    {
        $this->setData( 'scope_id', $scope_id );
        $this->scope_id = $scope_id;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return $this->getData( 'scope_id' );
    }

    /**
     * @param boolean $processed
     * @return $this
     */
    public function setProcessed($processed)
    {
        $this->setData( 'processed', $processed );
        $this->processed = $processed;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return boolean
     */
    public function getProcessed()
    {
        return $this->getData( 'processed' );
    }

    /**
     * @param boolean $is_updated
     * @return $this
     */
    public function setIsUpdated($is_updated)
    {
        $this->setData( 'is_updated', $is_updated );
        $this->is_updated = $is_updated;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsUpdated()
    {
        return $this->getData( 'is_updated' );
    }

    /**
     * @param boolean $is_failed
     * @return $this
     */
    public function setIsFailed($is_failed)
    {
        $this->setData( 'is_failed', $is_failed );
        $this->is_failed = $is_failed;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsFailed()
    {
        return $this->getData( 'is_failed' );
    }

    /**
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt($created_at)
    {
        $this->setData( 'created_at', $created_at );
        $this->created_at = $created_at;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData( 'created_at' );
    }

    /**
     * @param string $updated_at
     * @return $this
     */
    public function setUpdatedAt($updated_at)
    {
        $this->setData( 'updated_at', $updated_at );
        $this->updated_at = $updated_at;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData( 'updated_at' );
    }

    /**
     * @param string $checksum
     * @return $this
     */
    public function setChecksum($checksum)
    {
        $this->setData( 'checksum', $checksum );
        $this->checksum = $checksum;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getChecksum()
    {
        return $this->getData( 'checksum' );
    }

    /**
     * @param string $processed_at
     * @return $this
     */
    public function setProcessedAt($processed_at)
    {
        $this->setData( 'processed_at', $processed_at );
        $this->processed_at = $processed_at;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getProcessedAt()
    {
        return $this->getData( 'processed_at' );
    }


}
