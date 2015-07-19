<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SubgroupType Entity.
 */
class SubgroupType extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
   protected $_accessible = [
        _SUBGROUPTYPE_SUBGROUP_TYPE_NID => true,
        _SUBGROUPTYPE_SUBGROUP_TYPE_NAME => true,
        _SUBGROUPTYPE_SUBGROUP_TYPE_GID => true,
        _SUBGROUPTYPE_SUBGROUP_TYPE_ORDER => true,
        _SUBGROUPTYPE_SUBGROUP_TYPE_GLOBAL => true        
    ];
}
