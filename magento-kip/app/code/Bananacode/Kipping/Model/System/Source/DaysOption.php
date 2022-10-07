<?php

namespace Bananacode\Kipping\Model\System\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DaysOption
 * @package Bananacode\Kipping\Model\System\Source
 */
class DaysOption implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $days  = [
            [
                'label' => 'Lunes',
                'value' => '1'
            ],
            [
                'label' => 'Martes',
                'value' => '2'
            ],
            [
                'label' => 'Miércoles',
                'value' => '3'
            ],
            [
                'label' => 'Jueves',
                'value' => '4'
            ],
            [
                'label' => 'Viernes',
                'value' => '5'
            ],
            [
                'label' => 'Sábado',
                'value' => '6'
            ],
            [
                'label' => 'Domingo',
                'value' => '7'
            ]
        ];

        return $days;
    }
}
