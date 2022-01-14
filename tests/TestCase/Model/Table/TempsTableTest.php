<?php
declare(strict_types=1);

namespace CakeAes\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakeAes\Model\Table\TempsTable;

/**
 * CakeAes\Model\Table\TempsTable Test Case
 */
class TempsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \CakeAes\Model\Table\TempsTable
     */
    protected $Temps;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.CakeAes.Temps',
        'plugin.CakeAes.TempOthers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Temps') ? [] : ['className' => TempsTable::class];
        $this->Temps = $this->getTableLocator()->get('CakeAes.Temps', $config);
        Configure::write('Security.key', 'f9a73f2770c52dc4e2ce3eec60dc296745a33bfbfd06d1d8a9472de3afb72bc3');
        $temp = $this->Temps->get(1);
        $temp->nome = 'Vinícius Seixas';
        $this->Temps->save($temp);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Temps);

        parent::tearDown();
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf('CakeAes\Model\Table\TempsTable', $this->Temps);
    }      

    public function testBehavior(): void 
    {
        $this->assertTrue($this->Temps->hasBehavior('Encrypt'));
        $this->assertTrue($this->Temps->TempOthers->hasBehavior('Encrypt'));
    }

    public function testEncryptAndDecrypt(): void 
    {
        $nome = 'João Santos';
        $temp = $this->Temps->newEntity([
            'nome' => $nome
        ]);
        $temp = $this->Temps->save($temp);
        $this->assertEquals($nome, $temp->nome);

        $new = $this->Temps->get($temp->id, ['fields' => [
            'id', 
            'nome'
        ]]);   
        $this->assertEquals($nome, $new->nome);

        $new = $this->Temps->get($temp->id);   
        $this->assertEquals($nome, $new->nome);

        $temp = $this->Temps->find()
            ->select(['nome'])
            ->where(['id' => 2])
            ->first();   
        $this->assertEquals($nome, $temp->nome);

        $temp = $this->Temps->find()
            ->select(['nome'])
            ->where(['nome' => $nome])
            ->first();   
        $this->assertEquals($nome, $temp->nome);

        $temp = $this->Temps->find()
            ->select([
                'id', 
                'nome'
            ])
            ->where(['Temps.nome LIKE' => '%Sa%'])
            ->first();   
        $this->assertEquals($nome, $temp->nome);

        $temp = $this->Temps->get($temp->id);
        $temp->nome = 'Maria';
        $this->Temps->save($temp);        
        $update = $this->Temps->get($temp->id, ['fields' => ['nome']]);
        $this->assertEquals('Maria', $update->nome);

        $nome = $this->Temps->encrypt("José");        
        $fields = ['nome' => $nome];
        $conditions = [
            $this->Temps->decryptEq('Temps.nome', 'Maria')
        ];
        $this->Temps->updateAll($fields, $conditions);
        $update = $this->Temps->get($temp->id, ['fields' => ['nome']]);
        $this->assertEquals('José', $update->nome);        
    }

    public function testConditionsAndContainDecrypt(): void 
    {
        $nome = 'João Santos';
        $temp = $this->Temps->newEntity([
            'nome' => $nome
        ]);
        $temp = $this->Temps->save($temp);
        $this->assertEquals($nome, $temp->nome);
        
        $otherNome = 'Sônia Santos';
        $other = $this->Temps->TempOthers->newEntity([
            'nome' => $otherNome,
            'temp_id' => 2
        ]);
        $other = $this->Temps->TempOthers->save($other);
        $this->assertEquals($otherNome, $other->nome);

        $temp = $this->Temps->find()
            ->select([
                'id',
                'nome'
            ])
            ->where(['Temps.id' => 2])
            ->contain([
                'TempOthers' => ['fields' => [
                    'temp_id',
                    'nome'
                ]]
            ])
            ->first();
            
        $this->assertEquals($nome, $temp->nome);
        $this->assertEquals($otherNome, $temp->temp_others[0]->nome);

        $other = $this->Temps->TempOthers->find()
            ->select([
                'TempOthers.nome', 
                'TempOthers.temp_id'
            ])
            ->where(['TempOthers.id' => 2])
            ->contain(['Temps' => ['fields' => [
                'id',
                'nome'
            ]]])
            ->first();
        $this->assertEquals($nome, $other->temp->nome);
        $this->assertEquals($otherNome, $other->nome);        

        $other = $this->Temps->TempOthers->find()
            ->select([
                'temp_id'
            ])
            ->where([
                'TempOthers.id' => 2,
                'Temps.nome LIKE' => '%Jo%'
            ])
            ->contain(['Temps' => ['fields' => [
                'nome'
            ]]])
            ->first();
        $this->assertEquals($nome, $other->temp->nome);

        $other = $this->Temps->TempOthers->find()
            ->select([
                'TempOthers.temp_id'
            ])
            ->where([
                'TempOthers.id' => 2,
            ])
            ->contain(['Temps' => [
                'fields' => ['nome'],
                'conditions' => [
                    'Temps.nome LIKE' => '%Jo%'
                ]
            ]])
            ->first();
        $this->assertEquals($nome, $other->temp->nome);        
        
        $query = $this->Temps->TempOthers->find();
        $other = $query->select([
                'TempOthers.temp_id'
            ])
            ->where([
                'TempOthers.id' => 2,
            ])
            ->contain(['Temps' => [
                'fields' => ['nome'],
                'conditions' => [
                    $query->newExpr()->like('Temps.nome', '%Jo%')
                ]
            ]])
            ->first();
        $this->assertEquals($nome, $other->temp->nome);        
    }

    public function testOrderDecrypt(): void 
    {
        $nome = 'João Santos';
        $temp = $this->Temps->newEntity([
            'nome' => $nome
        ]);
        $temp = $this->Temps->save($temp);

        $temps = $this->Temps->find()
            ->order(['nome' => 'asc'])
            ->all()
            ->toArray();
        $this->assertEquals($nome, $temps[0]->nome);

        $temps = $this->Temps->find()
            ->order(['Temps.nome' => 'desc', 'id' => 'desc'])
            ->all()
            ->toArray();
        $this->assertEquals($nome, $temps[1]->nome);
    }

    public function testEncryptAndDecryptFile(): void 
    {
        $textFile = dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'Fixture' . DS . 'texto.txt';
        $imageFile = dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'Fixture' . DS . 'imagem.jpg';

        $done = $this->Temps->encryptFile($textFile);
        $this->assertTrue($done !== false);

        $done = $this->Temps->encryptFile($imageFile);
        $this->assertTrue($done !== false);

        $done = $this->Temps->decryptFile($textFile);
        $this->assertTrue($done !== false);

        $done = $this->Temps->decryptFile($imageFile);
        $this->assertTrue($done !== false);                                                      
    }    
}
