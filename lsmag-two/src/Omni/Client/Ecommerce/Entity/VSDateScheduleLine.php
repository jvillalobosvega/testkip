<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

class VSDateScheduleLine
{

    /**
     * @property string $EndingDate
     */
    protected $EndingDate = null;

    /**
     * @property boolean $Exclude
     */
    protected $Exclude = null;

    /**
     * @property int $LineNo
     */
    protected $LineNo = null;

    /**
     * @property string $StartingDate
     */
    protected $StartingDate = null;

    /**
     * @param string $EndingDate
     * @return $this
     */
    public function setEndingDate($EndingDate)
    {
        $this->EndingDate = $EndingDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndingDate()
    {
        return $this->EndingDate;
    }

    /**
     * @param boolean $Exclude
     * @return $this
     */
    public function setExclude($Exclude)
    {
        $this->Exclude = $Exclude;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getExclude()
    {
        return $this->Exclude;
    }

    /**
     * @param int $LineNo
     * @return $this
     */
    public function setLineNo($LineNo)
    {
        $this->LineNo = $LineNo;
        return $this;
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->LineNo;
    }

    /**
     * @param string $StartingDate
     * @return $this
     */
    public function setStartingDate($StartingDate)
    {
        $this->StartingDate = $StartingDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartingDate()
    {
        return $this->StartingDate;
    }


}

