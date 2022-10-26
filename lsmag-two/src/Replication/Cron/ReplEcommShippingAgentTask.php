<?php
/**
 * THIS IS AN AUTOGENERATED FILE
 * DO NOT MODIFY
 * @codingStandardsIgnoreFile
 */


namespace Ls\Replication\Cron;

use Ls\Replication\Logger\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config;
use Ls\Core\Helper\Data as LsHelper;
use Ls\Replication\Helper\ReplicationHelper;
use Ls\Omni\Client\Ecommerce\Entity\ReplRequest;
use Ls\Omni\Client\Ecommerce\Operation\ReplEcommShippingAgent;
use Ls\Replication\Api\ReplShippingAgentRepositoryInterface as ReplShippingAgentRepository;
use Ls\Replication\Model\ReplShippingAgentFactory;
use Ls\Replication\Api\Data\ReplShippingAgentInterface;

class ReplEcommShippingAgentTask extends AbstractReplicationTask
{

    public const JOB_CODE = 'replication_repl_shipping_agent';

    public const CONFIG_PATH = 'ls_mag/replication/repl_shipping_agent';

    public const CONFIG_PATH_STATUS = 'ls_mag/replication/status_repl_shipping_agent';

    public const CONFIG_PATH_LAST_EXECUTE = 'ls_mag/replication/last_execute_repl_shipping_agent';

    public const CONFIG_PATH_MAX_KEY = 'ls_mag/replication/max_key_repl_shipping_agent';

    /**
     * @property ReplShippingAgentRepository $repository
     */
    protected $repository = null;

    /**
     * @property ReplShippingAgentFactory $factory
     */
    protected $factory = null;

    /**
     * @property ReplShippingAgentInterface $data_interface
     */
    protected $data_interface = null;

    /**
     * @param ReplShippingAgentRepository $repository
     * @return $this
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return ReplShippingAgentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param ReplShippingAgentFactory $factory
     * @return $this
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @return ReplShippingAgentFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param ReplShippingAgentInterface $data_interface
     * @return $this
     */
    public function setDataInterface($data_interface)
    {
        $this->data_interface = $data_interface;
        return $this;
    }

    /**
     * @return ReplShippingAgentInterface
     */
    public function getDataInterface()
    {
        return $this->data_interface;
    }

    public function __construct(ScopeConfigInterface $scope_config, Config $resource_config, Logger $logger, LsHelper $helper, ReplicationHelper $repHelper, ReplShippingAgentFactory $factory, ReplShippingAgentRepository $repository, ReplShippingAgentInterface $data_interface)
    {
        parent::__construct($scope_config, $resource_config, $logger, $helper, $repHelper);
        $this->repository = $repository;
        $this->factory = $factory;
        $this->data_interface = $data_interface;
    }

    public function makeRequest($lastKey, $fullReplication = false, $batchSize = 100, $storeId = '', $maxKey = '', $baseUrl = '')
    {
        $request = new ReplEcommShippingAgent($baseUrl);
        $request->getOperationInput()
                 ->setReplRequest( ( new ReplRequest() )->setBatchSize($batchSize)
                                                        ->setFullReplication($fullReplication)
                                                        ->setLastKey($lastKey)
                                                        ->setMaxKey($maxKey)
                                                        ->setStoreId($storeId));
        return $request;
    }

    public function getConfigPath()
    {
        return self::CONFIG_PATH;
    }

    public function getConfigPathStatus()
    {
        return self::CONFIG_PATH_STATUS;
    }

    public function getConfigPathLastExecute()
    {
        return self::CONFIG_PATH_LAST_EXECUTE;
    }

    public function getConfigPathMaxKey()
    {
        return self::CONFIG_PATH_MAX_KEY;
    }

    public function getMainEntity()
    {
        return $this->data_interface;
    }


}

