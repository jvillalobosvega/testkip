<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bananacode\App\Model\Cart;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Quote\Model\Quote;

/**
 * Checks if guest checkout is allowed.
 */
class CheckCartCheckoutAllowance extends \Magento\QuoteGraphQl\Model\Cart\CheckCartCheckoutAllowance
{
    /**
     * Check if User is allowed to checkout
     *
     * @param Quote $quote
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function execute(Quote $quote): void
    {
        /*if (false === $quote->getCustomerIsGuest()) {
            return;
        }
        $isAllowedGuestCheckout = (bool)$this->checkoutHelper->isAllowedGuestCheckout($quote);
        if (false === $isAllowedGuestCheckout) {
            throw new GraphQlAuthorizationException(
                __(
                    'Guest checkout is not allowed. ' .
                    'Register a customer account or login with existing one.'
                )
            );
        }*/
    }
}
