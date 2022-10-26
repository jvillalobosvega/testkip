<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Replication\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Ls\Replication\Api\Data\ReplItemModifierInterface;

class ReplItemModifier extends AbstractModel implements ReplItemModifierInterface, IdentityInterface
{

    public const CACHE_TAG = 'ls_replication_repl_item_modifier';

    protected $_cacheTag = 'ls_replication_repl_item_modifier';

    protected $_eventPrefix = 'ls_replication_repl_item_modifier';

    /**
     * @property ItemModifierPriceHandling $AlwaysCharge
     */
    protected $AlwaysCharge = null;

    /**
     * @property float $AmountPercent
     */
    protected $AmountPercent = null;

    /**
     * @property string $Code
     */
    protected $Code = null;

    /**
     * @property string $Description
     */
    protected $Description = null;

    /**
     * @property string $ExplanatoryHeaderText
     */
    protected $ExplanatoryHeaderText = null;

    /**
     * @property int $GroupMaxSelection
     */
    protected $GroupMaxSelection = null;

    /**
     * @property int $GroupMinSelection
     */
    protected $GroupMinSelection = null;

    /**
     * @property string $nav_id
     */
    protected $nav_id = null;

    /**
     * @property boolean $IsDeleted
     */
    protected $IsDeleted = null;

    /**
     * @property int $MaxSelection
     */
    protected $MaxSelection = null;

    /**
     * @property int $MinSelection
     */
    protected $MinSelection = null;

    /**
     * @property ItemModifierPriceType $PriceType
     */
    protected $PriceType = null;

    /**
     * @property string $Prompt
     */
    protected $Prompt = null;

    /**
     * @property string $SubCode
     */
    protected $SubCode = null;

    /**
     * @property float $TimeModifierMinutes
     */
    protected $TimeModifierMinutes = null;

    /**
     * @property string $TriggerCode
     */
    protected $TriggerCode = null;

    /**
     * @property ItemTriggerFunction $TriggerFunction
     */
    protected $TriggerFunction = null;

    /**
     * @property ItemModifierType $Type
     */
    protected $Type = null;

    /**
     * @property string $UnitOfMeasure
     */
    protected $UnitOfMeasure = null;

    /**
     * @property ItemUsageCategory $UsageCategory
     */
    protected $UsageCategory = null;

    /**
     * @property string $VariantCode
     */
    protected $VariantCode = null;

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
        $this->_init( 'Ls\Replication\Model\ResourceModel\ReplItemModifier' );
    }

    public function getIdentities()
    {
        return [ self::CACHE_TAG . '_' . $this->getId() ];
    }

    /**
     * @param ItemModifierPriceHandling $AlwaysCharge
     * @return $this
     */
    public function setAlwaysCharge($AlwaysCharge)
    {
        $this->setData( 'AlwaysCharge', $AlwaysCharge );
        $this->AlwaysCharge = $AlwaysCharge;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return ItemModifierPriceHandling
     */
    public function getAlwaysCharge()
    {
        return $this->getData( 'AlwaysCharge' );
    }

    /**
     * @param float $AmountPercent
     * @return $this
     */
    public function setAmountPercent($AmountPercent)
    {
        $this->setData( 'AmountPercent', $AmountPercent );
        $this->AmountPercent = $AmountPercent;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountPercent()
    {
        return $this->getData( 'AmountPercent' );
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
     * @param string $ExplanatoryHeaderText
     * @return $this
     */
    public function setExplanatoryHeaderText($ExplanatoryHeaderText)
    {
        $this->setData( 'ExplanatoryHeaderText', $ExplanatoryHeaderText );
        $this->ExplanatoryHeaderText = $ExplanatoryHeaderText;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getExplanatoryHeaderText()
    {
        return $this->getData( 'ExplanatoryHeaderText' );
    }

    /**
     * @param int $GroupMaxSelection
     * @return $this
     */
    public function setGroupMaxSelection($GroupMaxSelection)
    {
        $this->setData( 'GroupMaxSelection', $GroupMaxSelection );
        $this->GroupMaxSelection = $GroupMaxSelection;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupMaxSelection()
    {
        return $this->getData( 'GroupMaxSelection' );
    }

    /**
     * @param int $GroupMinSelection
     * @return $this
     */
    public function setGroupMinSelection($GroupMinSelection)
    {
        $this->setData( 'GroupMinSelection', $GroupMinSelection );
        $this->GroupMinSelection = $GroupMinSelection;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupMinSelection()
    {
        return $this->getData( 'GroupMinSelection' );
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
     * @param int $MaxSelection
     * @return $this
     */
    public function setMaxSelection($MaxSelection)
    {
        $this->setData( 'MaxSelection', $MaxSelection );
        $this->MaxSelection = $MaxSelection;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSelection()
    {
        return $this->getData( 'MaxSelection' );
    }

    /**
     * @param int $MinSelection
     * @return $this
     */
    public function setMinSelection($MinSelection)
    {
        $this->setData( 'MinSelection', $MinSelection );
        $this->MinSelection = $MinSelection;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return int
     */
    public function getMinSelection()
    {
        return $this->getData( 'MinSelection' );
    }

    /**
     * @param ItemModifierPriceType $PriceType
     * @return $this
     */
    public function setPriceType($PriceType)
    {
        $this->setData( 'PriceType', $PriceType );
        $this->PriceType = $PriceType;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return ItemModifierPriceType
     */
    public function getPriceType()
    {
        return $this->getData( 'PriceType' );
    }

    /**
     * @param string $Prompt
     * @return $this
     */
    public function setPrompt($Prompt)
    {
        $this->setData( 'Prompt', $Prompt );
        $this->Prompt = $Prompt;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return $this->getData( 'Prompt' );
    }

    /**
     * @param string $SubCode
     * @return $this
     */
    public function setSubCode($SubCode)
    {
        $this->setData( 'SubCode', $SubCode );
        $this->SubCode = $SubCode;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getSubCode()
    {
        return $this->getData( 'SubCode' );
    }

    /**
     * @param float $TimeModifierMinutes
     * @return $this
     */
    public function setTimeModifierMinutes($TimeModifierMinutes)
    {
        $this->setData( 'TimeModifierMinutes', $TimeModifierMinutes );
        $this->TimeModifierMinutes = $TimeModifierMinutes;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return float
     */
    public function getTimeModifierMinutes()
    {
        return $this->getData( 'TimeModifierMinutes' );
    }

    /**
     * @param string $TriggerCode
     * @return $this
     */
    public function setTriggerCode($TriggerCode)
    {
        $this->setData( 'TriggerCode', $TriggerCode );
        $this->TriggerCode = $TriggerCode;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getTriggerCode()
    {
        return $this->getData( 'TriggerCode' );
    }

    /**
     * @param ItemTriggerFunction $TriggerFunction
     * @return $this
     */
    public function setTriggerFunction($TriggerFunction)
    {
        $this->setData( 'TriggerFunction', $TriggerFunction );
        $this->TriggerFunction = $TriggerFunction;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return ItemTriggerFunction
     */
    public function getTriggerFunction()
    {
        return $this->getData( 'TriggerFunction' );
    }

    /**
     * @param ItemModifierType $Type
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
     * @return ItemModifierType
     */
    public function getType()
    {
        return $this->getData( 'Type' );
    }

    /**
     * @param string $UnitOfMeasure
     * @return $this
     */
    public function setUnitOfMeasure($UnitOfMeasure)
    {
        $this->setData( 'UnitOfMeasure', $UnitOfMeasure );
        $this->UnitOfMeasure = $UnitOfMeasure;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getUnitOfMeasure()
    {
        return $this->getData( 'UnitOfMeasure' );
    }

    /**
     * @param ItemUsageCategory $UsageCategory
     * @return $this
     */
    public function setUsageCategory($UsageCategory)
    {
        $this->setData( 'UsageCategory', $UsageCategory );
        $this->UsageCategory = $UsageCategory;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return ItemUsageCategory
     */
    public function getUsageCategory()
    {
        return $this->getData( 'UsageCategory' );
    }

    /**
     * @param string $VariantCode
     * @return $this
     */
    public function setVariantCode($VariantCode)
    {
        $this->setData( 'VariantCode', $VariantCode );
        $this->VariantCode = $VariantCode;
        $this->setDataChanges( TRUE );
        return $this;
    }

    /**
     * @return string
     */
    public function getVariantCode()
    {
        return $this->getData( 'VariantCode' );
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
