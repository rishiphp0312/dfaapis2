<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LocationCustomFieldValue Entity.
 */
class LocationCustomFieldValue extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'value' => true,
        'location_custom_field_id' => true,
        'location_id' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'location_custom_field' => true,
        'location' => true,
        'modified_user' => true,
        'created_user' => true,
    ];
}
