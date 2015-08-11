<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * Unit Entity.
 */
class Language extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'Language_NId' => true,
        'Language_Name' => true,
        'Language_Code' => true,
        'Language_Default' => true,
        'Language_GlobalLock' => true
    ];
}
