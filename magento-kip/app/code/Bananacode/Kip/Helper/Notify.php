<?php

namespace Bananacode\Kip\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Notify extends AbstractHelper
{
    /**
     * @param $webhook
     * @param $content
     * @param $username
     */
    public function discord($webhook, $content, $username)
    {
        try {
            $webhookurl = $this->getConfig('bananacode/discord/' . $webhook);

            $json_data = json_encode([
                "content" => $content,
                "username" => $username,
                "tts" => false,

            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $ch = curl_init($webhookurl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {}
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getConfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
