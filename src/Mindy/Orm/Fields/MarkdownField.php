<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 02/05/14.05.2014 16:00
 */

namespace Mindy\Orm\Fields;

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

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\MarkdownField')
    {
        return parent::getFormField($form, $fieldClass);
    }
}
