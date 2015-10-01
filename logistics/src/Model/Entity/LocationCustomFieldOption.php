<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LocationCustomFieldOption Entity.
 */
class LocationCustomFieldOption extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'value' => true,
        'order' => true,
        'visible' => true,
        'location_custom_field_id' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'location_custom_field' => true,
        'modified_user' => true,
        'created_user' => true,
    ];
}
