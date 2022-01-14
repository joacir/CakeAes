<?php
declare(strict_types=1);

namespace CakeAes\Model\Entity;

use Cake\ORM\Entity;

/**
 * TempOther Entity
 *
 * @property int $id
 * @property int|null $temp_id
 * @property string|resource|null $nome
 *
 * @property \CakeAes\Model\Entity\Temp $temp
 */
class TempOther extends Entity
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
        'temp_id' => true,
        'nome' => true,
        'temp' => true,
    ];
}
