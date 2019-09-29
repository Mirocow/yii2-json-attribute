<?php
namespace mirocow\jsonattribute;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidArgumentException;
use yii\db\ActiveRecord;

/**
 *  @property ActiveRecord $owner
 */
class jsonAttributeBehavior extends Behavior
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var null|string
     */
    public $defaultValue = null;

    /**
     * @var bool
     */
    public $encodeBeforeValidation = true;


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => function ()
            {
                $this->initialization();
            },
            ActiveRecord::EVENT_AFTER_FIND => function ()
            {
                $this->decode();
            },
            ActiveRecord::EVENT_BEFORE_INSERT => function ()
            {
                $this->encode();
            },
            ActiveRecord::EVENT_BEFORE_UPDATE => function ()
            {
                $this->encode();
            },
            ActiveRecord::EVENT_AFTER_INSERT => function ()
            {
                $this->decode();
            },
            ActiveRecord::EVENT_AFTER_UPDATE => function ()
            {
                $this->decode();
            },
            ActiveRecord::EVENT_BEFORE_VALIDATE => function ()
            {
                if ($this->encodeBeforeValidation) {
                    $this->encodeValidate();
                }
            },
            ActiveRecord::EVENT_AFTER_VALIDATE => function ()
            {
                if ($this->encodeBeforeValidation) {
                    $this->decode();
                }
            },
        ];
    }

    /**
     *
     */
    protected function initialization()
    {
        foreach ($this->attributes as $attribute) {
            $this->owner->setAttribute($attribute, new JsonField());
        }
    }

    /**
     *
     */
    protected function decode()
    {
        foreach ($this->attributes as $attribute) {
            $value = $this->owner->getAttribute($attribute);
            if(empty($value)) {
                $this->owner->addError($attribute, Yii::t('app', 'Value hasn`t Json format'));
                continue;
            }
            if (!$value instanceof JsonField) {
                try {
                    $value = new JsonField($value);
                } catch (InvalidArgumentException $e){
                    $this->owner->addError($attribute, $e->getMessage());
                    continue;
                }
            }
            $this->owner->setAttribute($attribute, $value);
        }
    }

    /**
     *
     */
    protected function encode()
    {
        foreach ($this->attributes as $attribute) {
            $field = $this->owner->getAttribute($attribute);
            if (!$field instanceof JsonField) {
                $field = new JsonField($field);
            }
            $this->owner->setAttribute($attribute, (string)$field ?: $this->defaultValue);
        }
    }

    /**
     *
     */
    protected function encodeValidate()
    {
        foreach ($this->attributes as $attribute) {
            $field = $this->owner->getAttribute($attribute);
            if ($field instanceof JsonField) {
                $this->owner->setAttribute($attribute, (string)$field ?: null);
            }
        }
    }
}
