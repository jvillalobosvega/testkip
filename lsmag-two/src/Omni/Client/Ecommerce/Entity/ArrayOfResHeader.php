<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Omni\Client\Ecommerce\Entity;

use IteratorAggregate;
use ArrayIterator;

class ArrayOfResHeader implements IteratorAggregate
{

    /**
     * @property ResHeader[] $ResHeader
     */
    protected $ResHeader = [
        
    ];

    /**
     * @param ResHeader[] $ResHeader
     * @return $this
     */
    public function setResHeader($ResHeader)
    {
        $this->ResHeader = $ResHeader;
        return $this;
    }

    /**
     * @return ResHeader[]
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->ResHeader );
    }

    /**
     * @return ResHeader[]
     */
    public function getResHeader()
    {
        return $this->ResHeader;
    }


}

