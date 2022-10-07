<?php

namespace Bananacode\TaxDocument\Model;

class TaxCategory extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'bananacode_taxdocument_taxdocument';

	protected $_cacheTag = 'bananacode_taxdocument_taxcategory';

	protected $_eventPrefix = 'bananacode_taxdocument_taxcategory';

	protected function _construct()
	{
		$this->_init('Bananacode\TaxDocument\Model\ResourceModel\TaxCategory');
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