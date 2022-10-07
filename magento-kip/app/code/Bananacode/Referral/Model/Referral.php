<?php

namespace Bananacode\Referral\Model;

class Referral extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
	{
		$this->_init('Bananacode\Referral\Model\ResourceModel\Referral');
	}
}
