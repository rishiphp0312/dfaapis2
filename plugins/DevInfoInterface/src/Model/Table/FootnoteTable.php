<?php  
namespace DevInfoInterface\Model\Table;

use App\Model\Entity\Footnote;
use Cake\ORM\Table;


/**
 * Footnote Model
 */
class FootnoteTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('UT_FootNote_en');
        $this->primaryKey('FootNote_NId');
        $this->addBehavior('Timestamp');
    }

    /*
     * @Cakephp3: defaultConnectionName method
     * @Defines which DB connection to use from multiple database connections
     * @Connection Created in: CommonInterfaceComponent
     */
    public static function defaultConnectionName() {
        return 'devInfoConnection';
    }

    /**
     * setListTypeKeyValuePairs method
     *
     * @param array $fields The fields(keys/values) for the list.
     * @return void
     */
    public function setListTypeKeyValuePairs(array $fields)
    {
        $this->primaryKey($fields[0]);
        $this->displayField($fields[1]);
    }

    /**
     * getDataByParams method
     *
     * @param array $conditions The WHERE conditions for the Query. {DEFAULT : empty}
     * @param array $fields The Fields to SELECT from the Query. {DEFAULT : empty}
     * @return void
     */
    public function getDataByParams(array $fields, array $conditions, $type = 'all')
    {
        $options = [];

        if(!empty($fields))
            $options['fields'] = $fields;
        if(!empty($conditions))
            $options['conditions'] = $conditions;

        if($type == 'list') $this->setListTypeKeyValuePairs($fields);      
        $query = $this->find($type, $options);      
        $results = $query->hydrate(false)->all();
        $data = $results->toArray();

        return $data;
    }
}