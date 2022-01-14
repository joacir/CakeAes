<?php
declare(strict_types=1);

namespace CakeAes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TempOthers Model
 *
 * @property \CakeAes\Model\Table\TempsTable&\Cake\ORM\Association\BelongsTo $Temps
 *
 * @method \CakeAes\Model\Entity\TempOther newEmptyEntity()
 * @method \CakeAes\Model\Entity\TempOther newEntity(array $data, array $options = [])
 * @method \CakeAes\Model\Entity\TempOther[] newEntities(array $data, array $options = [])
 * @method \CakeAes\Model\Entity\TempOther get($primaryKey, $options = [])
 * @method \CakeAes\Model\Entity\TempOther findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \CakeAes\Model\Entity\TempOther patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CakeAes\Model\Entity\TempOther[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \CakeAes\Model\Entity\TempOther|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakeAes\Model\Entity\TempOther saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakeAes\Model\Entity\TempOther[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \CakeAes\Model\Entity\TempOther[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \CakeAes\Model\Entity\TempOther[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \CakeAes\Model\Entity\TempOther[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TempOthersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('temp_others');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->addBehavior('CakeAes.Encrypt', [
            'fields' => ['nome']
        ]);

        $this->belongsTo('Temps', [
            'foreignKey' => 'temp_id',
            'className' => 'CakeAes.Temps',
        ]);
    }
}
