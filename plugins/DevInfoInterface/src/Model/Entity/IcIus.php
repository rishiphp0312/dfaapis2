<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * IcIus Entity.
 */
class IcIus extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        _ICIUS_IC_IUSNID => true,
        _ICIUS_IC_NID => true,
        _ICIUS_IUSNID => true,
        _ICIUS_RECOMMENDEDSOURCE => true,
        _ICIUS_IC_IUS_ORDER => true,
        _ICIUS_IC_IUS_LABEL => true
    ];
}