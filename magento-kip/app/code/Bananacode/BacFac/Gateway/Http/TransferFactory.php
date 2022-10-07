<?php
/**
 * Copyright © 2019 Bananacode SA, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bananacode\BacFac\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class TransferFactory
 * @package Bananacode\BacFac\Gateway\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var Logger
     */
    private $logger;
    

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        
        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->logger = new \Monolog\Logger('Bacfac');
        $this->logger->pushHandler($logHandler);
        $this->logger->addInfo(print_r("TransferFactory CREATE", true));
        $this->logger->addInfo(print_r('------------------->'.json_encode($request), true));

        return $this->transferBuilder
            ->setBody($request)
            ->setMethod('POST')
            ->build();
    }
}
