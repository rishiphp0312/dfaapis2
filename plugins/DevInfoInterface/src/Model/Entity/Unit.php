<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * Unit Entity.
 */
class Unit extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'Unit_NId' => true,
        'Unit_Name' => true,
        'Unit_GId' => true,
        'Unit_Global' => true
    ];
}
