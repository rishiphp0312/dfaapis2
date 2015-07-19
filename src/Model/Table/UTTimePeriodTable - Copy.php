<?php  
namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Table;


/**
 * UTIndicatorEn Model
 */
class UTTimePeriodTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_TimePeriod');
        $this->primaryKey('TimePeriod_NId');
        //$this->addBehavior('Timestamp');
    }


    /**
     * getTimePeriodByIds method
     * @param array $id The WHERE conditions for the Query. {DEFAULT : null}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getTimePeriodById($id = null, array $fields, $type = 'all' )
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;

        $options['conditions'] = array('UTIndicatorEn.Indicator_NId'=>$id);

        // Find all the articles.
        // At this point the query has not run.
        $query = $this->find($type, $options);

        // Calling execute will execute the query
        // and return the result set.
        $results = $query->all();

        // Once we have a result set we can get all the rows
        $data = $results->toArray();
        return $data;
    }


    /**
     * getDataByParams method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $conditions, array $fields )
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;

        return $this->find('all', $options);

    }


}