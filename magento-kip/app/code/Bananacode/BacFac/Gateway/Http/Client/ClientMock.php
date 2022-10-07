<?php
/**
 * Copyright © 2019 Bananacode SA, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bananacode\BacFac\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class ClientMock
 * @package Bananacode\BacFac\Gateway\Http\Client
 */
class ClientMock implements ClientInterface
{
    const PROD_URL =    'https://marlin.firstatlanticcommerce.com/PGService/';

    const SANDBOX_URL = 'https://ecm.firstatlanticcommerce.com/PGService/';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $_curl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Bananacode\BacFac\Helper\Encryption
     */
    private $_bananaCryptor;

    /**
     * @var \Magento\Directory\Block\Currency
     */
    private $_currency;

    /**
     * @var \Bananacode\BacFac\Helper\Data
     */
    protected $_bananaHelper;

    /**
     * ClientMock constructor.
     * @param Logger $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bananacode\BacFac\Helper\Encryption $bananaCryptor
     * @param \Magento\Directory\Block\Currency $currency
     * @param \Bananacode\BacFac\Helper\Data $bananaHelper
     */
    public function __construct(
        Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bananacode\BacFac\Helper\Encryption $bananaCryptor,
        \Magento\Directory\Block\Currency $currency,
        \Bananacode\BacFac\Helper\Data $bananaHelper
    ) {
        $this->logger = $logger;
        $this->_curl = $curl;
        $this->_storeManager = $storeManager;
        $this->_bananaCryptor = $bananaCryptor;
        $this->_currency = $currency;
        $this->_bananaHelper = $bananaHelper;

     
    }

    /**
     * @param TransferInterface $transferObject
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->logger = new \Monolog\Logger('Bacfac');
        $this->logger->pushHandler($logHandler);
        $this->logger->addInfo(print_r("placeRequest", true));

        $requestData = $transferObject->getBody();
        $nonce = (array)json_decode($this->_bananaCryptor->decrypt($requestData['payment_method_nonce'], $requestData['ekey']));
        $requestData['is_authorized'] =  isset($requestData['is_authorized']) ? $requestData['is_authorized'] : false;

        //If authorized = 3DS was required (visa/mastercard)
        if ($requestData['is_authorized'] && $nonce['cc_type'] != 'AE') { 
            $this->logger->addInfo(print_r("AUTHORIZE RESPONSE VISA/MASTERCARD", true));          
            return $nonce;
        } else {
            //AMEX
            $response = $this->authorizeAndPlaceOrder($requestData, $nonce);
            
            if ($response) {
                $this->logger->addInfo(print_r("AUTHORIZE RESPONSE AMEX", true));
                $this->logger->addInfo(print_r(json_encode($response), true));
                $authorizeResult = $response->IsoResponseCode;
                $authorizeResultMessage = $response->ResponseMessage;
                // $creditCardTransactionResults = $authorizeResult->CreditCardTransactionResults;
                if ($authorizeResult == "SP4" && $authorizeResultMessage == 'SPI Preprocessing complete') {
                    // $nonce['TokenizedPAN'] = isset($creditCardTransactionResults->TokenizedPAN) ? $creditCardTransactionResults->TokenizedPAN : null;
                    // $nonce['PaddedCardNo'] = isset($creditCardTransactionResults->PaddedCardNumber) ? $creditCardTransactionResults->PaddedCardNumber : null;
                    // $nonce['OrderID'] = isset($authorizeResult->OrderNumber) ? $authorizeResult->OrderNumber : $requestData['order_id'];
                    $this->logger->addInfo(print_r("CCNS", true));
                    $this->logger->addInfo(print_r($nonce, true));
                    // unset($nonce['credit_card_number']);
                    // unset($nonce['credit_card_security_code_number']);
                    return $nonce;
                } else {
                    $requestData["error"] = $authorizeResultMessage->ReasonCodeDescription;
                    $requestData["response_code"] = $authorizeResult->ResponseCode;
                    /*if(
                        $creditCardTransactionResults->ReasonCodeDescription == "Duplicate Order Id not allowed."
                        || ($creditCardTransactionResults->ResponseCode == "40" && $creditCardTransactionResults->ReasonCode == "3")
                    ) {
                        //Order was already charged but something happened...
                    } */
                }
            } else {
                $requestData["error"] = "Unknown error authorizing payment.";
            }
        }
        $requestData["error"] = "Unknown error authorizing payment.";

        return $requestData;
    }

    /**
     * @param $requestData
     * @param $card_details
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function authorizeAndPlaceOrder($requestData, $card_details)
    {
        //$requestData["amount"]
        $logHandler = new \Monolog\Handler\RotatingFileHandler(BP . '/var/log/bacfac.log');
        $this->logger = new \Monolog\Logger('Bacfac');
        $this->logger->pushHandler($logHandler);
        $this->logger->addInfo(print_r("CLIENTMOCK authorizeAndPlaceOrder", true));

        return $this->_bananaHelper->generate_request(
            $card_details,
            $requestData["order_id"],
            null,
            false,
            $requestData['customer_id']
        );
    }
}
