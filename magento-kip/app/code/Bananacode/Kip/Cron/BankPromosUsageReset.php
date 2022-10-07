<?php

namespace Bananacode\Kip\Cron;

use Monolog\Handler\RotatingFileHandler;

/**
 *
 */
class BankPromosUsageReset
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_coupon;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_saleRule;


    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @param \Magento\SalesRule\Model\Rule $saleRule
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\SalesRule\Model\Rule $saleRule
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_coupon = $coupon;
        $this->_saleRule = $saleRule;

        $logHandler = new RotatingFileHandler(BP . '/var/log/bananacrons.log');
        $this->_logger = new \Monolog\Logger('Bananacrons');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     *
     */
    public function execute()
    {
        $this->_logger->info(print_r('---------------------------------', true));
        $this->_logger->info(print_r('----- BankPromosUsageReset ------', true));
        $this->_logger->info(print_r('---------------------------------', true));

        //todo: make this dynamic not hardcode coupons, sorry is christmas.

        try {
            $connection = $this->_resourceConnection->getConnection();
            $this->clearBankRuleCoupon($connection, 'BAC25%');
            $this->clearBankRuleCoupon($connection, 'CUSCA25%');
            $this->clearBankRuleCoupon($connection, 'PROME30%');
        } catch (\Exception $e) {
            $this->_logger->info(print_r($e->getMessage(), true));
        }
    }

    /**
     * @param $couponCode
     * @return int
     */
    private function getRuleId($couponCode)
    {
        return $this->_coupon->loadByCode($couponCode)->getRuleId() ?? 0;
    }

    /**
     * @param $connection
     * @param $couponCode
     */
    private function clearBankRuleCoupon($connection, $couponCode)
    {
        $ruleId = $this->getRuleId($couponCode);

        $sql = "select coupon_id from salesrule_coupon where rule_id = " . $ruleId . ";";
        $couponId = $connection->fetchOne($sql);

        $sql = "update salesrule_customer set times_used = 0 where rule_id = " . $ruleId . ";";
        $result1 = $connection->query($sql);
        $this->_logger->info(print_r($couponCode . ' salesrule_customer affected rows: ' . $result1->rowCount(), true));

        if($couponId) {
            $sql = "update salesrule_coupon_usage set times_used = 0 where coupon_id = " . $couponId . ";";
            $result2 = $connection->query($sql);
            $this->_logger->info(print_r($couponCode . ' salesrule_coupon_usage affected rows: ' . $result2->rowCount(), true));
        }
    }
}
