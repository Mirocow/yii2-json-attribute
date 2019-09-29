<?php
namespace mirocow\jsonattribute;

use Yii;
use yii\base\Arrayable;
use yii\base\InvalidParamException;
use yii\helpers\Json;

class JsonField implements \ArrayAccess, \Iterator, \Countable, Arrayable
{
    /**
     * @var array
     */
    protected $value;


    /**
     * @param string|array $value
     */
    public function __construct($value = [])
    {
        $this->set($value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value ? Json::encode($this->value) : '[]';
    }

    /**
     * @param string|array $value
     */
    public function set($value)
    {
        if ($value === null || $value === '') {
            $value = [];
        } elseif (is_string($value)) {
            $value = Json::decode($value, true);
            if (!is_array($value)) {
                throw new InvalidParamException(Yii::t('app', 'Value is scalar'));
            }
        }
        if (!is_array($value)) {
            throw new InvalidParamException(Yii::t('app', 'Value is not array'));
        } else {
            $this->value = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = array_keys($this->value);
        return array_combine($fields, $fields);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return empty($fields) ? $this->value : array_intersect_key($this->value, array_flip($fields));
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->value;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->value[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function &offsetGet($offset)
    {
        $null = null;
        if (isset($this->value[$offset])) {
            return $this->value[$offset];
        } else {
            return $null;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current($this->value);
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        next($this->value);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return !is_null($this->key());
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key($this->value);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        reset($this->value);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->value);
    }
}
