<?php
namespace DevInfoInterface\Model\Entity;

use Cake\ORM\Entity;

/**
 * IndicatorClassifications Entity.
 */
class IndicatorClassifications extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'IC_NId' => true,
        'IC_Parent_NId' => true,
        'IC_GId' => true,
        'IC_Name' => true,
        'IC_Global' => true,
        'IC_Info' => true,
        'IC_Type' => true,
        'IC_Short_Name' => true,
        'Publisher' => true,
        'Title' => true,
        'DIYear' => true,
        'SourceLink1' => true,
        'SourceLink2' => true,
        'IC_Order' => true,
        'ISBN' => true,
        'Nature' => true
    ];
}
