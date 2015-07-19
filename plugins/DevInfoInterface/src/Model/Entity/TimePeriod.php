<?php  
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TimePeriod Entity.
 */
class TimePeriod extends Entity
{

  /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
	  protected $_accessible = [
        _TIMEPERIOD_TIMEPERIOD_NID => true,
        _TIMEPERIOD_TIMEPERIOD => true,
        _TIMEPERIOD_STARTDATE => true,
        _TIMEPERIOD_ENDDATE => true,
        _TIMEPERIOD_PERIODICITY => true
    ];
   

}