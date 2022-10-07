<?php

namespace Bananacode\Referral\Model\ResourceModel\Referral;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Bananacode\Referral\Model\Referral', 'Bananacode\Referral\Model\ResourceModel\Referral');
	}

}
