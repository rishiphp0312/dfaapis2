<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MTransactionLog Entity.
 */
class MTransactionLog extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'db_id' => true,
        'action' => true,
        'module' => true,
        'submodule' => true,
        'identifier' => true,
        'previousvalue' => true,
        'newvalue' => true,
        'status' => true,
        'description' => true,
        'user' => true,
        'db' => true,
    ];
}
