<?php

namespace Bananacode\BacFac\Cron;

use Monolog\Handler\RotatingFileHandler;

/**
 *
 */
class UpdateVaultPaymentToken
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
     * UpdateVaultPaymentToken. constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->_resourceConnection = $resourceConnection;

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
        $this->_logger->info(print_r('--- UpdateVaultPaymentToken ----', true));
        $this->_logger->info(print_r('---------------------------------', true));

        $connection = $this->_resourceConnection->getConnection();

        $this->_logger->info(print_r('Delete inactive...', true));
        $sql = "DELETE FROM vault_payment_token WHERE is_active = 0 OR is_visible = 0;";
        $connection->query($sql);

        $sql = "SELECT * from vault_payment_token;";
        $vaults = $connection->fetchAll($sql);
        $this->_logger->info(print_r('Check BINS...', true));
        foreach ($vaults as $vault) {
            if(isset($vault['details'])) {
                $details = json_decode($vault['details'], true);
                if(is_array($details)) {
                    if(!isset($details['bin'])) {
                        $bin = explode('_', $vault['gateway_token'])[0];
                        $details['bin'] = $bin;
                        $details = json_encode($details);
                        $sql = "UPDATE vault_payment_token set details = '" . $details . "' where entity_id = ". $vault['entity_id'] . ";";
                        $connection->query($sql);
                        $this->_logger->info(print_r('BIN update: ' .  $vault['entity_id'] . ':' . $bin, true));
                    }
                }
            }
        }
    }
}
