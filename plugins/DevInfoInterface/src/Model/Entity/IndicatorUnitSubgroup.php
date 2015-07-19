<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * IndicatorUnitSubgroup Entity.
 */
class IndicatorUnitSubgroup extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'IUSNId' => true,
        'Indicator_NId' => true,
        'Unit_NId' => true,
        'Subgroup_Val_NId' => true,
        'Min_Value' => true,
        'Max_Value' => true,
        'Subgroup_Nids' => true,
        'Data_Exist' => true,
        'IsDefaultSubgroup' => true,
        'AvlMinDataValue' => true,
        'AvlMaxDataValue' => true,
        'AvlMinTimePeriod' => true,
        'AvlMaxTimePeriod' => true
    ];
}
