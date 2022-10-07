<?php

namespace Bananacode\Kip\Model;


class Widget
{
    /**
     * @param \Magento\Widget\Model\Widget $subject
     * @param $type
     * @param array $params
     * @param bool $asIs
     * @return array
     */
    public function beforeGetWidgetDeclaration(
        \Magento\Widget\Model\Widget $subject,
        $type,
        $params = [],
        $asIs = true
    ) {
        if(key_exists("image", $params)) {
            $url = $params["image"];
            if(strpos($url,'/directive/___directive/') !== false) {
                $parts = explode('/', $url);
                $key   = array_search("___directive", $parts);
                if($key !== false) {

                    $url = $parts[$key+1];
                    $url = base64_decode(strtr($url, '-_,', '+/='));

                    $parts = explode('"', $url);
                    $key   = array_search("{{media url=", $parts);
                    $url   = $parts[$key+1];

                    $params["image"] = $url;
                }
            }
        }

        if(key_exists("image_mobile", $params)) {
            $url = $params["image_mobile"];
            if(strpos($url,'/directive/___directive/') !== false) {
                $parts = explode('/', $url);
                $key   = array_search("___directive", $parts);
                if($key !== false) {

                    $url = $parts[$key+1];
                    $url = base64_decode(strtr($url, '-_,', '+/='));

                    $parts = explode('"', $url);
                    $key   = array_search("{{media url=", $parts);
                    $url   = $parts[$key+1];

                    $params["image_mobile"] = $url;
                }
            }
        }

        return array($type, $params, $asIs);
    }
}
