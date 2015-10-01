<?php
namespace App\Model\Table;

use App\Model\Entity\ShipmentLocationAttachment;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ShipmentLocationAttachments Model
 */
class ShipmentLocationAttachmentsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('shipment_location_attachments');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Shipments', [
            'foreignKey' => 'shipment_id',
            'joinType' => 'INNER'
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
            ->allowEmpty('attachment_type');
            
        $validator
            ->allowEmpty('attachment');

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
}
