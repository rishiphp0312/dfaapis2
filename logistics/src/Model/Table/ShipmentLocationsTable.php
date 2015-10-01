<?php
namespace App\Model\Table;

use App\Model\Entity\ShipmentLocation;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ShipmentLocations Model
 */
class ShipmentLocationsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('shipment_locations');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Shipments', [
            'foreignKey' => 'shipment_id'
        ]);
        $this->belongsTo('Couriers', [
            'foreignKey' => 'courier_id'
        ]);
        $this->belongsTo('Statuses', [
            'foreignKey' => 'status_id'
        ]);
        $this->belongsTo('locations', [
            'foreignKey' => 'to_location_id'
        ]);
         $this->belongsTo('Areas', [
            'foreignKey' => 'to_area_id'
        ]);
          $this->belongsTo('Couriers', [
            'foreignKey' => 'courier_id'
        ]);
         
         
        
        
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->allowEmpty('sequence_no');
            
        $validator
            ->add('expected_delivery_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('expected_delivery_date');
            
        $validator
            ->allowEmpty('delivery_latitude');
            
        $validator
            ->allowEmpty('delivery_longitude');
            
        $validator
            ->allowEmpty('delivery_comments');
            
        $validator
            ->allowEmpty('confirmation_comments');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['shipment_id'], 'Shipments'));
        $rules->add($rules->existsIn(['courier_id'], 'Couriers'));
        $rules->add($rules->existsIn(['status_id'], 'Statuses'));
        return $rules;
    }

    /**
     * Insert Single Row
     *
     * @param array $fieldsArray Fields to insert with their Data. {DEFAULT : empty}
     * @return integer last inserted ID if true else 0
      $extra if extra is nid then returns nid else gives gid
     */
    public function saveRecords($fieldsArray = []) {
        //Create New Entity
        $shipmentLoc = $this->newEntity();

        //Update New Entity Object with data
        $shipmentLoc = $this->patchEntity($shipmentLoc, $fieldsArray);

        //Create new row and Save the Data
        $result = $this->save($shipmentLoc);
        if ($result) {
            return $result->id;
        } else {
            return 0;
        }
    }

    /**
     * Insert/Update multiple rows at once (runs multiple queries for multiple records)
     *
     * @param array $dataArray Data rows to insert. {DEFAULT : empty}
     * @return void
     */
    public function saveBulkRecords($dataArray = []) {
        // IF only one record being inserted/updated
        if (count($dataArray) == 1) {
            return $this->saveRecords(reset($dataArray));
        }

        // Remove any Duplicate entry
        $dataArray = array_intersect_key($dataArray, array_unique(array_map('serialize', $dataArray)));

        //Create New Entities (multiple entities for multiple rows/records)
        $entities = $this->newEntities($dataArray);

        foreach ($entities as $entity) {
            if (!$entity->errors()) {
                //Create new row and Save the Data
                $this->save($entity);
            }
        }
    }
    
    /**
     *method to get specific delivery point  
     * $id delivery id 
    */
    public function getDeliveryPoint($id = null) {
     
        $options=[];
        if($id!='')
        $options['conditions']=['ShipmentLocations.id' => $id];     
       
        return $data= $this->find('all',$options )->contain(['Shipments','locations','Areas','Couriers'])->hydrate(false)->first();
    }
    
    
    /**
     *method to get all delivery point/confirmation  list  with their corresponding details 
     * $confirmation_id id of yes in case of confirmations  
    */
   /* public function getDeliveryList($confirmation,$confirmation_id='') {
       // $options['fields']=[];
        $options=[];
      
        if($confirmation==true && $confirmation_id!=''){
            $options['conditions']=['ShipmentLocations.confirmation_id' => $confirmation_id];
        }
        return $data = $this->find('all',$options )->contain(['Shipments','locations','Areas','Couriers'])->hydrate(false)->all()->toArray();
        
    }*/
     public function getDeliveryDetails($conditions=[],$contain=[]) {
       // $options['fields']=[];
        $options=[];
        $options['conditions']=$conditions;
        $options['order']= ['sequence_no'=>'ASC'];
        
        return $data = $this->find('all',$options )->contain($contain)->hydrate(false)->all()->toArray();
        
    }
    
    
    /**
     *method to get all delivery point list  with respect to shipment id
     * $shipmentId shipment Id 
    */
    public function getDeliveryPointsDetails($conditions=[]) {
        $options=[];
      
        if(!empty($conditions))
        $options['conditions']=$conditions;
        
        $options['order']= ['sequence_no'=>'ASC'];
      
        return $data = $this->find('all',$options )->contain(['locations','Couriers'])->hydrate(false)->all()->toArray();
        
    }
    
     /**
     * 
     * method to delete the shipments   
     * @conditions array 
     */
    public function deleteRecords(array $conditions) {
        $result = $this->deleteAll($conditions);
        if ($result > 0)
            return $result;
        else
            return 0;
    }

}
