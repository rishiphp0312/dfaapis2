<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Courier Entity.
 */
class Courier extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'code' => true,
        'name' => true,
        'login' => true,
        'contact' => true,
        'phone' => true,
        'fax' => true,
        'email' => true,
        'comments' => true,
        'status_id' => true,
        'type_id' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'status' => true,
        'type' => true,
        'modified_user' => true,
        'created_user' => true,
        'courier_custom_field_values' => true,
        'shipment_locations' => true,
    ];
}
