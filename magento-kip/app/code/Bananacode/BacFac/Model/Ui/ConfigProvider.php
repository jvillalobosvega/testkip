<?php
/**
 * Copyright © 2019 Bananacode SA, All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bananacode\BacFac\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\Config\Config;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'bacfac';

    const VAULT_CODE = 'bacfac_vault';

    const PROD_URL =    'https://marlin.firstatlanticcommerce.com/PGService/';

    const SANDBOX_URL = 'https://ecm.firstatlanticcommerce.com/PGService/';

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_encryptor = $encryptor;
        $this->config = $config;
        $this->config->setMethodCode(self::CODE);
        $this->_storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $this->config->setMethodCode(self::CODE);

        $sandbox = $this->config->getValue('sandbox');

        $ekey = str_split($this->config->getValue('acquirer_id') . $this->config->getValue('merchant_id'),3);

        $binsBAC = $this->config->getValue('bac_bins_numbers');
        $binsCUS = $this->config->getValue('cus_bins_numbers');
        $binsPROME = $this->config->getValue('prome_bins_numbers');
        $binsAGRICOLA = $this->config->getValue('agricola_bins_numbers');
        $exemptProductId = $this->config->getValue('exempt_product_id');

        return [
            'payment' => [
                self::CODE => [
                    'sandbox' => $sandbox,
                    'ekey' => $ekey[0] . $ekey[1] . $ekey[2],
                    'api_url' => $sandbox ? self::SANDBOX_URL : self::PROD_URL,
                    'ccVaultCode' => self::VAULT_CODE,
                    'bacfac_url' => $this->_storeManager->getStore()->getUrl('bacfac/checkout/authorize'),
                    'bins' => $binsBAC . ',' . $binsCUS . ',' . $binsPROME. ',' . $binsAGRICOLA,
                    'exemptProductId' => $exemptProductId,
                ]
            ]
        ];
    }
}
