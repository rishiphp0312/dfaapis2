<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AreaLevel Entity.
 */
class AreaLevel extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'level' => true,
		'comments' => true,
		'visible' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'modified_user' => true,
        'created_user' => true,
        'areas' => true,
    ];
}
