<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Subgroup Entity.
 */
class Subgroup extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
   protected $_accessible = [
        _SUBGROUP_SUBGROUP_NID => true,
        _SUBGROUP_SUBGROUP_NAME => true,
        _SUBGROUP_SUBGROUP_GID => true,
        _SUBGROUP_SUBGROUP_GLOBAL => true,
        _SUBGROUP_SUBGROUP_TYPE => true,      
        _SUBGROUP_SUBGROUP_ORDER => true       
    ];
}
