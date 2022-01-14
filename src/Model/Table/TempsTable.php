<?php
declare(strict_types=1);

namespace CakeAes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Temps Model
 *
 * @property \CakeAes\Model\Table\TempOthersTable&\Cake\ORM\Association\HasMany $TempOthers
 *
 * @method \CakeAes\Model\Entity\Temp newEmptyEntity()
 * @method \CakeAes\Model\Entity\Temp newEntity(array $data, array $options = [])
 * @method \CakeAes\Model\Entity\Temp[] newEntities(array $data, array $options = [])
 * @method \CakeAes\Model\Entity\Temp get($primaryKey, $options = [])
 * @method \CakeAes\Model\Entity\Temp findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \CakeAes\Model\Entity\Temp patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \CakeAes\Model\Entity\Temp[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \CakeAes\Model\Entity\Temp|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakeAes\Model\Entity\Temp saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \CakeAes\Model\Entity\Temp[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \CakeAes\Model\Entity\Temp[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \CakeAes\Model\Entity\Temp[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \CakeAes\Model\Entity\Temp[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TempsTable extends Table
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

        $this->setTable('temps');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->addBehavior('CakeAes.Encrypt', [
            'fields' => ['nome', 'cpf']
        ]);

        $this->hasMany('TempOthers', [
            'foreignKey' => 'temp_id',
            'className' => 'CakeAes.TempOthers',
        ]);
    }
}
