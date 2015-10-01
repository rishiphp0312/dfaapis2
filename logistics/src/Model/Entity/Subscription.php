<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Subscription Entity.
 */
class Subscription extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'email' => true,
        'mobile' => true,
        'alert' => true,
        'area_id' => true,
        'location_id' => true,
        'comments' => true,
        'createdBy' => true,
        'modifiedBy' => true,
        'area' => true,
        'location' => true,
    ];
}
