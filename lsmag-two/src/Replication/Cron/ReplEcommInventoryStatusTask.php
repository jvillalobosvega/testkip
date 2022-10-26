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
use Ls\Omni\Client\Ecommerce\Operation\ReplEcommInventoryStatus;
use Ls\Replication\Api\ReplInvStatusRepositoryInterface as ReplInvStatusRepository;
use Ls\Replication\Model\ReplInvStatusFactory;
use Ls\Replication\Api\Data\ReplInvStatusInterface;

class ReplEcommInventoryStatusTask extends AbstractReplicationTask
{

    public const JOB_CODE = 'replication_repl_inv_status';

    public const CONFIG_PATH = 'ls_mag/replication/repl_inv_status';

    public const CONFIG_PATH_STATUS = 'ls_mag/replication/status_repl_inv_status';

    public const CONFIG_PATH_LAST_EXECUTE = 'ls_mag/replication/last_execute_repl_inv_status';

    public const CONFIG_PATH_MAX_KEY = 'ls_mag/replication/max_key_repl_inv_status';

    /**
     * @property ReplInvStatusRepository $repository
     */
    protected $repository = null;

    /**
     * @property ReplInvStatusFactory $factory
     */
    protected $factory = null;

    /**
     * @property ReplInvStatusInterface $data_interface
     */
    protected $data_interface = null;

    /**
     * @param ReplInvStatusRepository $repository
     * @return $this
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return ReplInvStatusRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param ReplInvStatusFactory $factory
     * @return $this
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @return ReplInvStatusFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param ReplInvStatusInterface $data_interface
     * @return $this
     */
    public function setDataInterface($data_interface)
    {
        $this->data_interface = $data_interface;
        return $this;
    }

    /**
     * @return ReplInvStatusInterface
     */
    public function getDataInterface()
    {
        return $this->data_interface;
    }

    public function __construct(ScopeConfigInterface $scope_config, Config $resource_config, Logger $logger, LsHelper $helper, ReplicationHelper $repHelper, ReplInvStatusFactory $factory, ReplInvStatusRepository $repository, ReplInvStatusInterface $data_interface)
    {
        parent::__construct($scope_config, $resource_config, $logger, $helper, $repHelper);
        $this->repository = $repository;
        $this->factory = $factory;
        $this->data_interface = $data_interface;
    }

    public function makeRequest($lastKey, $fullReplication = false, $batchSize = 100, $storeId = '', $maxKey = '', $baseUrl = '')
    {
        $request = new ReplEcommInventoryStatus($baseUrl);
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
