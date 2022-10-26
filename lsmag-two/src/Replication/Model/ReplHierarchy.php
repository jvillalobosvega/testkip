<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Replication\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Ls\Replication\Api\Data\ReplHierarchyInterface;

class ReplHierarchy extends AbstractModel implements ReplHierarchyInterface, IdentityInterface
{

    public const CACHE_TAG = 'ls_replication_repl_hierarchy';

    protected $_cacheTag = 'ls_replication_repl_hierarchy';

    protected $_eventPrefix = 'ls_replication_repl_hierarchy';

    /**
     * @property string $Description
     */
    protected $Description = null;

    /**
     * @property string $nav_id
     */
    protected $nav_id = null;

    /**
     * @property boolean $IsDeleted
     */
    protected $IsDeleted = null;

    /**
     * @property int $Priority
     */
    protected $Priority = null;

    /**
     * @property string $SalesType
     */
    protected $SalesType = null;

    /**
     * @property string $StartDate
     */
    protected $StartDate = null;

    /**
     * @property HierarchyType $Type
     */
    protected $Type = null;

    /**
     * @property string $ValidationScheduleId
     */
    protected $ValidationScheduleId = null;

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
        $this->_init( 'Ls\Replication\Model\ResourceModel\ReplHierarchy' );
    }

    public function getIdentities()
    {
        return [ self::CACHE_TAG . '_' . $this->getId() ];
    }

    /**
     * @param string $Description
     * @return $this
     */
    public function setDescription($Description)
    {
        $this->setData( 'Description', $Description );
        $this->Description = $Description;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData( 'Description' );
    }

    /**
     * @param string $nav_id
     * @return $this
     */
    public function setNavId($nav_id)
    {
        $this->setData( 'nav_id', $nav_id );
        $this->nav_id = $nav_id;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getNavId()
    {
        return $this->getData( 'nav_id' );
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
     * @param int $Priority
     * @return $this
     */
    public function setPriority($Priority)
    {
        $this->setData( 'Priority', $Priority );
        $this->Priority = $Priority;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->getData( 'Priority' );
    }

    /**
     * @param string $SalesType
     * @return $this
     */
    public function setSalesType($SalesType)
    {
        $this->setData( 'SalesType', $SalesType );
        $this->SalesType = $SalesType;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getSalesType()
    {
        return $this->getData( 'SalesType' );
    }

    /**
     * @param string $StartDate
     * @return $this
     */
    public function setStartDate($StartDate)
    {
        $this->setData( 'StartDate', $StartDate );
        $this->StartDate = $StartDate;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->getData( 'StartDate' );
    }

    /**
     * @param HierarchyType $Type
     * @return $this
     */
    public function setType($Type)
    {
        $this->setData( 'Type', $Type );
        $this->Type = $Type;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return HierarchyType
     */
    public function getType()
    {
        return $this->getData( 'Type' );
    }

    /**
     * @param string $ValidationScheduleId
     * @return $this
     */
    public function setValidationScheduleId($ValidationScheduleId)
    {
        $this->setData( 'ValidationScheduleId', $ValidationScheduleId );
        $this->ValidationScheduleId = $ValidationScheduleId;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationScheduleId()
    {
        return $this->getData( 'ValidationScheduleId' );
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
