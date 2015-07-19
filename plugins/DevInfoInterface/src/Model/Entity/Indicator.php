<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * Indicator Entity.
 */
class Indicator extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        _INDICATOR_INDICATOR_NID => true,
        _INDICATOR_INDICATOR_NAME => true,
        _INDICATOR_INDICATOR_GID => true,
        _INDICATOR_INDICATOR_INFO => true,
        _INDICATOR_INDICATOR_GLOBAL => true,
        _INDICATOR_SHORT_NAME => true,
        _INDICATOR_KEYWORDS => true,
        _INDICATOR_INDICATOR_ORDER => true,
        _INDICATOR_DATA_EXIST => true,
        _INDICATOR_HIGHISGOOD => true
    ];
}