<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Module Entity.
 */
class Module extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'parent_id' => true,
        'title' => true,
        'active' => true,
        'parent_module' => true,
        'child_modules' => true,
        'roles' => true,
    ];
}
