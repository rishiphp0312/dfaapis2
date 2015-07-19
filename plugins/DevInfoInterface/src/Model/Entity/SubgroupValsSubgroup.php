<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * Indicator Entity.
 */
class SubgroupValsSubgroup extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_SUBGROUP_NID => true,
        _SUBGROUP_VALS_SUBGROUP_SUBGROUP_VAL_NID => true,
        SUBGROUP_VALS_SUBGROUP_SUBGROUP_NID => true
    ];
}