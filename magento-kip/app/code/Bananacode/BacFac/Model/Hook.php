<?php
// CODITY PENDING TESTING
namespace Bananacode\BacFac\Model;

use Magento\Store\Model\Store;
use Bananacode\BacFac\Api\HookInterface;

class Hook implements HookInterface
{
    const XML_PATH_TRANS_EMAIL_GENERAL_EMAIL = 'trans_email/ident_general/email';

    const XML_PATH_TRANS_EMAIL_GENERAL_NAME = 'trans_email/ident_general/name';

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryFactory
     */
    protected $_orderHistoryFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Monolog\Logger
     */
    private $_logger;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_orderRepository = $orderRepository;
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerRepository = $customerRepository;
        $this->_scopeConfig = $scopeConfig;

        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->_logger = new \Monolog\Logger('Bacfac');
        $this->_logger->pushHandler($logHandler);
    }

    /**
     * Send email on payment status update
     *
     * @param string $id
     * @return mixed
     */
    public function notify($id) {
        $hookResponse = file_get_contents('php://input');
        $response = (Array)json_decode($hookResponse, true);

        $searchCriteria =
            $this->_searchCriteriaBuilder
                ->addFilter('document_id', $id)
                ->create();
        $orderList = $this->_orderRepository->getList($searchCriteria)->getItems();
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         */
        $order = reset($orderList);
        if($order && isset($response['amount'])) {
            $billingAddress = $order->getBillingAddress();
            $paymentInformation = $order->getPayment()->getAdditionalInformation();
            if(is_array($paymentInformation) && $billingAddress) {
                if(isset($paymentInformation['response'])) {
                    date_default_timezone_set('America/El_Salvador');
                    $paymentData = json_decode($paymentInformation['response'], true);
                    $this->sendEmail(
                        array_merge($paymentData, $response, [
                            'subject' => 'Kip: Voucher Digital',
                            'document_id' => $id,
                            'first_name' => $billingAddress->getFirstname(),
                            'name' => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
                            'email' => $billingAddress->getEmail(),
                            'date' => date('m/d/Y', time()),
                            'hour' =>  date('h:i:s a', time())
                        ])
                    );
                }
            }
        }
    }

    /**
     * @param $data
     */
    private function sendEmail($data)
    {
        try {
            $transport =
                $this->_transportBuilder
                    ->setTemplateIdentifier('bacfac_notify')
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => Store::DISTRO_STORE_ID
                        ]
                    )
                    ->setTemplateVars(
                        $data
                    )
                    ->setFromByScope(
                        [
                            'email' => $this->_scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                Store::DISTRO_STORE_ID
                            ),
                            'name' => $this->_scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                Store::DISTRO_STORE_ID
                            ),
                        ],
                        1
                    )
                    ->addTo(
                        $data['email'],
                        $data['name']
                    )
                    ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $ex) {

        }
    }
}
