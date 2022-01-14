<?php
declare(strict_types=1);

namespace CakeAes\Model\Entity;

use Cake\ORM\Entity;

/**
 * Temp Entity
 *
 * @property int $id
 * @property string|resource|null $nome
 * @property string|resource|null $cpf
 *
 * @property \CakeAes\Model\Entity\TempOther[] $temp_others
 */
class Temp extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'nome' => true,
        'cpf' => true,
        'temp_others' => true,
    ];
}
