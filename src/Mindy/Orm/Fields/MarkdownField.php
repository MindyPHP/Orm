<?php

namespace Mindy\Orm\Fields;

/**
 * Class MarkdownField
 * @package Mindy\Orm
 */
class MarkdownField extends TextField
{
    public function setValue($value)
    {
        foreach($this->getExtraFieldsInit() as  $field) {
            $field->setValue($value);
        }
        return parent::setValue($value);
    }

    public function getExtraFields()
    {
        return [
            $this->name . '_html' => [
                'class' => MarkdownHtmlField::className(),
                'editable' => false
            ]
        ];
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\MarkdownField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
