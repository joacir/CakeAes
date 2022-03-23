<?php
declare(strict_types=1);

namespace CakeAes\Model\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BinaryType;

/**
 * Binary type converter.
 *
 * Use to convert AES_ENCRYPT data between PHP and the database types.
 */
//class AesType extends BinaryType implements ExpressionTypeInterface
class AesType extends BinaryType
{
    /**
     * Convert varbinary into resource handles
     *
     * @param mixed $value The value to convert.
     * @param \Cake\Database\DriverInterface $driver The driver instance to convert with.
     * @return resource|null
     * @throws \Cake\Core\Exception\Exception
     */
    public function toPHP($value, DriverInterface $driver)
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value) || is_numeric($value) || is_resource($value)) {
            return (string) $value;
        }
        throw new \Exception(sprintf('Unable to convert %s into binary.', gettype($value)));
    }
}
