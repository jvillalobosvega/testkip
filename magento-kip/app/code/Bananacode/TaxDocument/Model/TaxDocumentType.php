<?php

namespace Bananacode\TaxDocument\Model;

class TaxDocumentType extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'bananacode_taxdocument_taxdocumenttype';

    protected $_cacheTag = 'bananacode_taxdocument_taxdocumenttype';

    protected $_eventPrefix = 'bananacode_taxdocument_taxdocumenttype';

    protected function _construct()
    {
        $this->_init('Bananacode\TaxDocument\Model\ResourceModel\TaxDocumentType');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
