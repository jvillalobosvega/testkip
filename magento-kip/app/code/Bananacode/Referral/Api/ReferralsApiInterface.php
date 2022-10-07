<?php

namespace Bananacode\Referral\Api;

interface ReferralsApiInterface
{
    /**
     * GET customer referral data
     *
     * @return mixed
     */
    public function getCustomerReferralData();

    /**
     * GET customer referral data
     *
     * @param string $id
     * @return mixed
     */
    public function getCustomerReferralDataAdmin($id);

    /**
     * SET customer referral cash
     *
     * @param string $id
     * @param float $cash
     * @return mixed
     */
    public function setCustomerReferralCash($id, $cash);
}
