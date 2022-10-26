<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

use Ls\Omni\Client\Ecommerce\Entity\Enum\ItemModifierPriceHandling;
use Ls\Omni\Client\Ecommerce\Entity\Enum\ItemModifierPriceType;
use Ls\Omni\Client\Ecommerce\Entity\Enum\ItemTriggerFunction;
use Ls\Omni\Client\Ecommerce\Entity\Enum\ItemModifierType;
use Ls\Omni\Client\Ecommerce\Entity\Enum\ItemUsageCategory;
use Ls\Omni\Exception\InvalidEnumException;

class ReplItemModifier
{

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
     * @property string $Id
     */
    protected $Id = null;

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
     * @param ItemModifierPriceHandling|string $AlwaysCharge
     * @return $this
     * @throws InvalidEnumException
     */
    public function setAlwaysCharge($AlwaysCharge)
    {
        if ( ! $AlwaysCharge instanceof ItemModifierPriceHandling ) {
            if ( ItemModifierPriceHandling::isValid( $AlwaysCharge ) )
                $AlwaysCharge = new ItemModifierPriceHandling( $AlwaysCharge );
            elseif ( ItemModifierPriceHandling::isValidKey( $AlwaysCharge ) )
                $AlwaysCharge = new ItemModifierPriceHandling( constant( "ItemModifierPriceHandling::$AlwaysCharge" ) );
            elseif ( ! $AlwaysCharge instanceof ItemModifierPriceHandling )
                throw new InvalidEnumException();
        }
        $this->AlwaysCharge = $AlwaysCharge->getValue();

        return $this;
    }

    /**
     * @return ItemModifierPriceHandling
     */
    public function getAlwaysCharge()
    {
        return $this->AlwaysCharge;
    }

    /**
     * @param float $AmountPercent
     * @return $this
     */
    public function setAmountPercent($AmountPercent)
    {
        $this->AmountPercent = $AmountPercent;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountPercent()
    {
        return $this->AmountPercent;
    }

    /**
     * @param string $Code
     * @return $this
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->Code;
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
     * @param string $ExplanatoryHeaderText
     * @return $this
     */
    public function setExplanatoryHeaderText($ExplanatoryHeaderText)
    {
        $this->ExplanatoryHeaderText = $ExplanatoryHeaderText;
        return $this;
    }

    /**
     * @return string
     */
    public function getExplanatoryHeaderText()
    {
        return $this->ExplanatoryHeaderText;
    }

    /**
     * @param int $GroupMaxSelection
     * @return $this
     */
    public function setGroupMaxSelection($GroupMaxSelection)
    {
        $this->GroupMaxSelection = $GroupMaxSelection;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupMaxSelection()
    {
        return $this->GroupMaxSelection;
    }

    /**
     * @param int $GroupMinSelection
     * @return $this
     */
    public function setGroupMinSelection($GroupMinSelection)
    {
        $this->GroupMinSelection = $GroupMinSelection;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupMinSelection()
    {
        return $this->GroupMinSelection;
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
     * @param boolean $IsDeleted
     * @return $this
     */
    public function setIsDeleted($IsDeleted)
    {
        $this->IsDeleted = $IsDeleted;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->IsDeleted;
    }

    /**
     * @param int $MaxSelection
     * @return $this
     */
    public function setMaxSelection($MaxSelection)
    {
        $this->MaxSelection = $MaxSelection;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxSelection()
    {
        return $this->MaxSelection;
    }

    /**
     * @param int $MinSelection
     * @return $this
     */
    public function setMinSelection($MinSelection)
    {
        $this->MinSelection = $MinSelection;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinSelection()
    {
        return $this->MinSelection;
    }

    /**
     * @param ItemModifierPriceType|string $PriceType
     * @return $this
     * @throws InvalidEnumException
     */
    public function setPriceType($PriceType)
    {
        if ( ! $PriceType instanceof ItemModifierPriceType ) {
            if ( ItemModifierPriceType::isValid( $PriceType ) )
                $PriceType = new ItemModifierPriceType( $PriceType );
            elseif ( ItemModifierPriceType::isValidKey( $PriceType ) )
                $PriceType = new ItemModifierPriceType( constant( "ItemModifierPriceType::$PriceType" ) );
            elseif ( ! $PriceType instanceof ItemModifierPriceType )
                throw new InvalidEnumException();
        }
        $this->PriceType = $PriceType->getValue();

        return $this;
    }

    /**
     * @return ItemModifierPriceType
     */
    public function getPriceType()
    {
        return $this->PriceType;
    }

    /**
     * @param string $Prompt
     * @return $this
     */
    public function setPrompt($Prompt)
    {
        $this->Prompt = $Prompt;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return $this->Prompt;
    }

    /**
     * @param string $SubCode
     * @return $this
     */
    public function setSubCode($SubCode)
    {
        $this->SubCode = $SubCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubCode()
    {
        return $this->SubCode;
    }

    /**
     * @param float $TimeModifierMinutes
     * @return $this
     */
    public function setTimeModifierMinutes($TimeModifierMinutes)
    {
        $this->TimeModifierMinutes = $TimeModifierMinutes;
        return $this;
    }

    /**
     * @return float
     */
    public function getTimeModifierMinutes()
    {
        return $this->TimeModifierMinutes;
    }

    /**
     * @param string $TriggerCode
     * @return $this
     */
    public function setTriggerCode($TriggerCode)
    {
        $this->TriggerCode = $TriggerCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getTriggerCode()
    {
        return $this->TriggerCode;
    }

    /**
     * @param ItemTriggerFunction|string $TriggerFunction
     * @return $this
     * @throws InvalidEnumException
     */
    public function setTriggerFunction($TriggerFunction)
    {
        if ( ! $TriggerFunction instanceof ItemTriggerFunction ) {
            if ( ItemTriggerFunction::isValid( $TriggerFunction ) )
                $TriggerFunction = new ItemTriggerFunction( $TriggerFunction );
            elseif ( ItemTriggerFunction::isValidKey( $TriggerFunction ) )
                $TriggerFunction = new ItemTriggerFunction( constant( "ItemTriggerFunction::$TriggerFunction" ) );
            elseif ( ! $TriggerFunction instanceof ItemTriggerFunction )
                throw new InvalidEnumException();
        }
        $this->TriggerFunction = $TriggerFunction->getValue();

        return $this;
    }

    /**
     * @return ItemTriggerFunction
     */
    public function getTriggerFunction()
    {
        return $this->TriggerFunction;
    }

    /**
     * @param ItemModifierType|string $Type
     * @return $this
     * @throws InvalidEnumException
     */
    public function setType($Type)
    {
        if ( ! $Type instanceof ItemModifierType ) {
            if ( ItemModifierType::isValid( $Type ) )
                $Type = new ItemModifierType( $Type );
            elseif ( ItemModifierType::isValidKey( $Type ) )
                $Type = new ItemModifierType( constant( "ItemModifierType::$Type" ) );
            elseif ( ! $Type instanceof ItemModifierType )
                throw new InvalidEnumException();
        }
        $this->Type = $Type->getValue();

        return $this;
    }

    /**
     * @return ItemModifierType
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param string $UnitOfMeasure
     * @return $this
     */
    public function setUnitOfMeasure($UnitOfMeasure)
    {
        $this->UnitOfMeasure = $UnitOfMeasure;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnitOfMeasure()
    {
        return $this->UnitOfMeasure;
    }

    /**
     * @param ItemUsageCategory|string $UsageCategory
     * @return $this
     * @throws InvalidEnumException
     */
    public function setUsageCategory($UsageCategory)
    {
        if ( ! $UsageCategory instanceof ItemUsageCategory ) {
            if ( ItemUsageCategory::isValid( $UsageCategory ) )
                $UsageCategory = new ItemUsageCategory( $UsageCategory );
            elseif ( ItemUsageCategory::isValidKey( $UsageCategory ) )
                $UsageCategory = new ItemUsageCategory( constant( "ItemUsageCategory::$UsageCategory" ) );
            elseif ( ! $UsageCategory instanceof ItemUsageCategory )
                throw new InvalidEnumException();
        }
        $this->UsageCategory = $UsageCategory->getValue();

        return $this;
    }

    /**
     * @return ItemUsageCategory
     */
    public function getUsageCategory()
    {
        return $this->UsageCategory;
    }

    /**
     * @param string $VariantCode
     * @return $this
     */
    public function setVariantCode($VariantCode)
    {
        $this->VariantCode = $VariantCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getVariantCode()
    {
        return $this->VariantCode;
    }

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param int $scope_id
     * @return $this
     */
    public function setScopeId($scope_id)
    {
        $this->scope_id = $scope_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return $this->scope_id;
    }


}
