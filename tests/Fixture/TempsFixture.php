<?php
declare(strict_types=1);

namespace CakeAes\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TempsFixture
 */
class TempsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // phpcs:disable
    public $fields = [
        'id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'nome' => ['type' => 'binary', 'length' => 255, 'null' => true, 'default' => '', 'comment' => '', 'precision' => null],
        'cpf' => ['type' => 'binary', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // phpcs:enable

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'nome' => 'Vinícius',
                'cpf' => '12345678900',
            ],
        ];

        parent::init();
    }
}
