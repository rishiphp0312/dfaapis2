<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Indicator Entity.
 */
class Indicator extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'Indicator_NId' => true,
        'Indicator_Name' => true,
        'Indicator_GId' => true,
        'Indicator_Info' => true,
        'Indicator_Global' => true,
        'Short_Name' => true,
        'Keywords' => true,
        'Indicator_Order' => true,
        'Data_Exist' => true,
        'HighIsGood' => true
    ];
}
