<?php

namespace Bananacode\Kipping\Model\System\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class HoursOption
 * @package Bananacode\Kipping\Model\System\Source
 */
class HoursOption implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $days = [
            ["value" => "0", "label" => "12:00 am"],
            ["value" => "1", "label" => "1:00 am"],
            ["value" => "2", "label" => "2:00 am"],
            ["value" => "3", "label" => "3:00 am"],
            ["value" => "4", "label" => "4:00 am"],
            ["value" => "5", "label" => "5:00 am"],
            ["value" => "6", "label" => "6:00 am"],
            ["value" => "7", "label" => "7:00 am"],
            ["value" => "8", "label" => "8:00 am"],
            ["value" => "9", "label" => "9:00 am"],
            ["value" => "10", "label" => "10:00 am"],
            ["value" => "11", "label" => "11:00 am"],
            ["value" => "12", "label" => "12:00 pm"],
            ["value" => "13", "label" => "1:00 pm"],
            ["value" => "14", "label" => "2:00 pm"],
            ["value" => "15", "label" => "3:00 pm"],
            ["value" => "16", "label" => "4:00 pm"],
            ["value" => "17", "label" => "5:00 pm"],
            ["value" => "18", "label" => "6:00 pm"],
            ["value" => "19", "label" => "7:00 pm"],
            ["value" => "20", "label" => "8:00 pm"],
            ["value" => "21", "label" => "9:00 pm"],
            ["value" => "22", "label" => "10:00 pm"],
            ["value" => "23", "label" => "11:00 pm"]
        ];

        return $days;
    }
}
