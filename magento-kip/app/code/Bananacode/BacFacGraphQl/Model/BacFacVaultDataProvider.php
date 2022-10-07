<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bananacode\BacFacGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Format BacFac input into value expected when setting payment method
 */
class BacFacVaultDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'bacfac_vault';

    /**
     * Format BacFac input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        if (!isset($args[self::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "bacfac_vault" for "payment_method" is missing.')
            );
        }

        if (!isset($args[self::PATH_ADDITIONAL_DATA]['public_hash'])) {
            throw new GraphQlInputException(
                __('Required parameter "public_hash" for "bacfac_vault" is missing.')
            );
        }

        return $args[self::PATH_ADDITIONAL_DATA];
    }
}
