<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Location Entity.
 */
class Location extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'alternative_name' => true,
        'comments' => true,
        'code' => true,
        'address' => true,
        'postal_code' => true,
        'contact_person' => true,
        'telephone' => true,
        'fax' => true,
        'email' => true,
        'website' => true,
        'date_opened' => true,
        'date_closed' => true,
        'longitude' => true,
        'latitude' => true,
        'area_id' => true,
        'locality_id' => true,
        'type_id' => true,
        'status_id' => true,
        'ownership_id' => true,
        'sector_id' => true,
        'provider_id' => true,
        'gender_id' => true,
        'security_group_id' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'area' => true,
        'locality' => true,
        'type' => true,
        'status' => true,
        'ownership' => true,
        'sector' => true,
        'provider' => true,
        'gender' => true,
        'security_group' => true,
        'modified_user' => true,
        'created_user' => true,
        'location_custom_field_values' => true,
        'subscriptions' => true,
    ];
}
