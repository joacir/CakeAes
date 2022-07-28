<?php
declare(strict_types=1);

namespace CakeAes\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Utility\Security;
use Cake\Database\TypeFactory;
use Cake\ORM\Locator\LocatorAwareTrait;
/**
 * Encrypt behavior
 */
class EncryptBehavior extends Behavior
{
    use LocatorAwareTrait;

    public function initialize(array $config): void
    {
        $this->_table->encryptFields = [];
        $this->_table->decryptedValues = [];
        $this->_table->containEncryptedFields = null;
        if (!empty($config['fields'])) {
            $this->_table->encryptFields = $config['fields'];
            TypeFactory::map('aes', 'CakeAes\Model\Database\Type\AesType');
            $schema = $this->_table->getSchema();
            foreach ($config['fields'] as $field) {
                $schema->setColumnType($field, 'aes');
            }
        }
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        foreach ($this->_table->encryptFields as $field) {
            $value = $entity->get($field);
            if (!empty($value)) {
                $this->_table->decryptedValues[$field] = $value;
                $entity->set($field, $this->encrypt($value));
            }
        }
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        foreach ($this->_table->decryptedValues as $field => $value) {
            $entity->set($field, $value);
        }
        $this->_table->decryptedValues = [];
    }

    /**
     * Criptografa uma string de um campo
     *
     * @param string $value valor do campo
     * @return QueryExpression Expressão SQL para criptografia do valor
     */
    public function encrypt(string $value): QueryExpression
    {
        $key = Configure::read('Security.key');
        $query = $this->_table->find();
        $value = addslashes($value);
        $expressionValue = $query->newExpr()
            ->add("AES_ENCRYPT('{$value}',UNHEX('{$key}'))");

        return $expressionValue;
	}

    public function beforeFind(EventInterface $event, Query $query, ArrayObject $options, bool $primary): void
    {
        $associations = $query->getContain();
        $this->setContainFields($query, $associations);
        $query = $this->decryptSelect($query, $primary);
        $query = $this->decryptWhere($query);
        $query = $this->decryptOrder($query);
    }

    protected function setContainFields(Query $query, array $associations): void
    {
        foreach ($associations as $name => $config) {
            foreach ($config as $key => $options) {
                if ($key === 'fields') {
                    /** @var \Cake\ORM\Locator\TableLocator $locator */
                    $locator = $this->getTableLocator();
                    $table = $locator->allowFallbackClass(true)
                        ->get($name);
                    if ($table->hasBehavior('Encrypt')) {
                        $table->containEncryptedFields = $options;
                    }
                } else {
                    if (is_array($options)) {
                        $this->setContainFields($query, [$key => $options]);
                    }
                }
            }
        }
    }

    /**
     * Descriptografa os campos criptografados da clausula select
     *
     * @param Query $query query para descriptografia
     * @param bool $primary verifica se o query é a inicial ou é uma associação
     * @return Query retorna a query com os campos da select modificados
     */
    public function decryptSelect(Query $query, $primary): Query
    {
        $select = $query->clause('select');
        if (empty($select)) {
            if ($primary || $this->_table->containEncryptedFields === null) {
                $select = $this->_table
                    ->getSchema()
                    ->columns();
            } else {
                $select = $this->_table->containEncryptedFields;
                $this->_table->containEncryptedFields = [];
            }
        }
        $fields = [];
        foreach ($select as $virtual => $field) {
            if ($this->isEncrypted($field)) {
                $table = $this->_table->getAlias();
                if (strpos($field, '.') !== false) {
                    list($table, $field) = explode('.', $field);
                }
                $virtual = \is_numeric($virtual) ? $table . '__' . $field : $virtual;
                $fields[$virtual] = $this->decryptField($table . '.' . $field);
            } else {
                $fields[$virtual] = $field;
            }
        }
        if (!empty($fields)) {
            $query = $query->select($fields, true);
        }

        return $query;
    }

    /**
     * Descriptografa os campos criptografados da clausula where
     *
     * @param Query $query query para descriptografia
     * @return Query retorna a query com os campos da where modificados
     */
    public function decryptWhere(Query $query): Query
    {
        $expr = $query->clause('where');
        if (!empty($expr)) {
            $expr->traverse(function ($condition) {
                if (is_a($condition, 'Cake\Database\Expression\ComparisonExpression')) {
                    $field = $condition->getField();
                    if (is_string($field) && $this->isEncrypted($field)) {
                        $condition->setField($this->decryptField($field));
                    }
                }

                return $condition;
            });
        }

        return $query;
    }

    /**
     * Descriptografa os campos criptografados da clausula order by
     *
     * @param Query $query query para descriptografia
     * @return Query retorna a query com os campos do order by modificados
     */
    public function decryptOrder(Query $query): Query
    {
        $expr = $query->clause('order');
        if (!empty($expr)) {
            $expr->iterateParts(function ($direction, &$field) {
                if ($this->isEncrypted($field)) {
                    $field = $this->decryptString($field);
                }

                return $direction;
            });
        }

        return $query;
    }

    /**
     * Verifica se é um campo criptografado
     *
     * @param string $field nome do campo para verificação
     * @return bool retorna true se é um campo criptografado
     */
    public function isEncrypted($field): bool
    {
        $isEncrypted = false;
        if (!empty($field) && is_string($field)) {
            $table = null;
            if (strpos($field, '.') !== false) {
                list($table, $field) = explode('.', $field);
            }
            if (empty($table) || $table == $this->_table->getAlias()) {
                $isEncrypted = in_array($field, $this->_table->encryptFields);
            } else {
                if (!empty($this->_table->{$table}) && $this->_table->{$table}->hasBehavior('Encrypt')) {
                    $isEncrypted = $this->_table->{$table}->isEncrypted($field);
                }
            }
        }

        return $isEncrypted;
    }

    /**
     * Expressão de comparação com campo descriptografado
     *
     * @param string $fieldName nome do campo
     * @param string $value valor para comparação
     * @return QueryExpression Expressão SQL para comparação do campo descriptografado
     */
    public function decryptEq(string $fieldName, string $value): QueryExpression
    {
        $query = $this->_table->find();
        $expressioEquals = $query->newExpr()
            ->eq($this->decryptField($fieldName), $value);

        return $expressioEquals;
    }

    public function decryptNotEq(string $fieldName, string $value): QueryExpression
    {
        $query = $this->_table->find();
        $expressioEquals = $query->newExpr()
            ->notEq($this->decryptField($fieldName), $value);

        return $expressioEquals;
    }

    public function decryptLike(string $fieldName, string $value): QueryExpression
    {
        $query = $this->_table->find();
        $expressioEquals = $query->newExpr()
            ->like($this->decryptField($fieldName), $value);

        return $expressioEquals;
    }

    public function decryptNotLike(string $fieldName, string $value): QueryExpression
    {
        $query = $this->_table->find();
        $expressioEquals = $query->newExpr()
            ->notLike($this->decryptField($fieldName), $value);

        return $expressioEquals;
    }

    public function decryptIn(string $fieldName, string $value): QueryExpression
    {
        $query = $this->_table->find();
        $expressioEquals = $query->newExpr()
            ->in($this->decryptField($fieldName), $value);

        return $expressioEquals;
    }

    public function decryptNotIn(string $fieldName, string $value): QueryExpression
    {
        $query = $this->_table->find();
        $expressioEquals = $query->newExpr()
            ->notIn($this->decryptField($fieldName), $value);

        return $expressioEquals;
    }

    /**
     * Descriptografa um campo
     *
     * @param string $fieldName nome do campo
     * @return QueryExpression Expressão SQL para descriptografia do campo
     */
    public function decryptField($fieldName): QueryExpression
    {
        $expressionField = $this->_table->find()
            ->newExpr()
            ->add($this->decryptString($fieldName));

        return $expressionField;
    }

    public function decryptString(string $fieldName): string
    {
        $key = Configure::read('Security.key');
        $expression = "(CONVERT(AES_DECRYPT({$fieldName}, UNHEX('{$key}')) USING utf8) COLLATE utf8_general_ci)";

        return $expression;
    }

    /**
     * Criptografa um arquivo
     *
     * @param string $pathFileName caminho e nome do arquivo
     * @return false|int quantidade de bytes gravados
     */
    public function encryptFile(string $pathFileName)
    {
		$crypted = false;
        if (file_exists($pathFileName)) {
            $content = @file_get_contents($pathFileName);
            if (!empty($content)) {
                $key = Configure::read('Security.key');
                $content = Security::encrypt($content, $key);
                if (!empty($content)) {
                    $crypted = @file_put_contents($pathFileName, $content);
                }
            }
        }

		return $crypted;
	}

    /**
     * Descriptografa um arquivo
     *
     * @param string $pathFileName caminho e nome do arquivo
     * @return false|int quantidade de bytes gravados
     */
    public function decryptFile(string $pathFileName)
    {
		$decrypted = false;
        if (file_exists($pathFileName)) {
            $content = @file_get_contents($pathFileName);
            if (!empty($content)) {
                $key = Configure::read('Security.key');
                $content = Security::decrypt($content, $key);
                if (!empty($content)) {
                    $decrypted = @file_put_contents($pathFileName, $content);
                }
            }
        }

		return $decrypted;
	}
}
