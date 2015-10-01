<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ConfigItemOption Entity.
 */
class ConfigItemOption extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'option_type' => true,
        'option' => true,
        'value' => true,
        'order' => true,
        'visible' => true,
    ];
}
