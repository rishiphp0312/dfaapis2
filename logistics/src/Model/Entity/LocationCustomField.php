<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LocationCustomField Entity.
 */
class LocationCustomField extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'order' => true,
        'type' => true,
        'visible' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'modified_user' => true,
        'created_user' => true,
        'location_custom_field_options' => true,
        'location_custom_field_values' => true,
    ];
}
