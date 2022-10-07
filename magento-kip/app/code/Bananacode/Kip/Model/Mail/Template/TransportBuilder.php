<?php

namespace Bananacode\Kip\Model\Mail\Template;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @param $pdfString
     * @param $filename
     * @return \Zend\Mime\Part
     */
    public function addAttachment($pdfString, $filename)
    {
        $attachment = new \Zend\Mime\Part($pdfString);
        $attachment->type = \Zend_Mime::TYPE_OCTETSTREAM;
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = $filename;
        return $attachment;
    }
}
