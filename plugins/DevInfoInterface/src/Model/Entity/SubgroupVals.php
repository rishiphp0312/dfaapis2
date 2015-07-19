<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * Indicator Entity.
 */
class SubgroupVals extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        _SUBGROUP_SUBGROUP_VAL_NID => true,
        _SUBGROUP_SUBGROUP_VAL => true,
        _SUBGROUP_SUBGROUP_VAL_GID => true,
        _SUBGROUP_SUBGROUP_VAL_GLOBAL => true,
        _SUBGROUP_SUBGROUP_VAL_ORDER => true 
    ];
}
