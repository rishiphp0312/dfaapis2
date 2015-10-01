<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RolesModule Entity.
 */
class RolesModule extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'role_id' => true,
        'module_id' => true,
        'create' => true,
        'read' => true,
        'update' => true,
        'delete' => true,
        'role' => true,
        'module' => true,
    ];
}
