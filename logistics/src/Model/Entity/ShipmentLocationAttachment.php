<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ShipmentLocationAttachment Entity.
 */
class ShipmentLocationAttachment extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'shipment_id' => true,
        'sequence_no' => true,
        'attachment_type' => true,
        'attachment' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'shipment' => true,
        'modified_user' => true,
        'created_user' => true,
    ];
}
