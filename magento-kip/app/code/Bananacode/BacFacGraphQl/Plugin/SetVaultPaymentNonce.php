<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bananacode\BacFacGraphQl\Plugin;

use Bananacode\BacFac\Gateway\Command\GetPaymentNonceCommand;
use Bananacode\BacFac\Model\Ui\ConfigProvider;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Psr\Log\LoggerInterface;

/**
 * Plugin creating nonce from Magento Vault BacFac public hash
 */
class SetVaultPaymentNonce
{
    /**
     * @var GetPaymentNonceCommand
     */
    private $command;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetPaymentNonceCommand $command
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetPaymentNonceCommand $command,
        LoggerInterface $logger
    ) {
        $this->command = $command;
        $this->logger = $logger;
    }

    /**
     * Set BacFac nonce from public hash
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $paymentData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject,
        \Magento\Quote\Model\Quote $quote,
        array $paymentData
    ): array {
        if ($paymentData['code'] !== ConfigProvider::VAULT_CODE
            || !isset($paymentData[ConfigProvider::VAULT_CODE])
            || !isset($paymentData[ConfigProvider::VAULT_CODE]['public_hash'])
        ) {
            return [$quote, $paymentData];
        }

        $subject = [
            'public_hash' => $paymentData[ConfigProvider::VAULT_CODE]['public_hash'],
            'customer_id' => $quote->getCustomerId(),
            'store_id' => $quote->getStoreId(),
        ];

        try {
            $result = $this->command->execute($subject)->get();
            $paymentData[ConfigProvider::VAULT_CODE]['payment_method_nonce'] = $result['paymentMethodNonce'];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlInputException(__('Sorry, but something went wrong'));
        }

        return [$quote, $paymentData];
    }
}
