<?php

namespace Bananacode\Referral\Model\ResourceModel;

class Referral extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}

	protected function _construct()
	{
		$this->_init('bananacode_referrals', 'id');
	}

}
