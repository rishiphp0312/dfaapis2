<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MIusValidation Entity.
 */
class MIusValidation extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        _MIUSVALIDATION_ID => true,
        _MIUSVALIDATION_DB_ID => true,
        _MIUSVALIDATION_INDICATOR_GID => true,
        _MIUSVALIDATION_UNIT_GID => true,
        _MIUSVALIDATION_SUBGROUP_GID => true,
        _MIUSVALIDATION_IS_TEXTUAL => true,
        _MIUSVALIDATION_MIN_VALUE => true,
        _MIUSVALIDATION_MAX_VALUE => true,
        _MIUSVALIDATION_CREATEDBY => true,
        _MIUSVALIDATION_MODIFIEDBY => true
    ];
}
