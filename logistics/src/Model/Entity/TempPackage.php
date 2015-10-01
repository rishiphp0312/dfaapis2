<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TempPackage Entity.
 */
class TempPackage extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'shipment_code' => true,
        'cnt' => true,
    ];
}
