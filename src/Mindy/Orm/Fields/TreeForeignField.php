<?php

namespace Mindy\Orm\Fields;

use Mindy\Helper\Creator;
use Mindy\Validation\RequiredValidator;

/**
 * Class TreeForeignField
 * @package Mindy\Orm
 */
class TreeForeignField extends ForeignField
{
    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DropDownField', array $extra = [])
    {
        $relatedModel = $this->getRelatedModel();

        $choices = function () use ($relatedModel) {
            $list = ['' => ''];

            $qs = $relatedModel->objects()->order(['root', 'lft']);
            $parents = $qs->all();
            foreach ($parents as $model) {
                $level = $model->level ? $model->level - 1 : $model->level;
                $list[$model->pk] = $level ? str_repeat("..", $level) . ' ' . $model->name : $model->name;
            }
            return $list;
        };

        if ($this->primary || $this->editable === false) {
            return null;
        }

        if ($fieldClass === null) {
            $fieldClass = $this->choices ? \Mindy\Form\Fields\DropDownField::className() : \Mindy\Form\Fields\CharField::className();
        } elseif ($fieldClass === false) {
            return null;
        }

        $model = $this->getModel();

        $disabled = [];
        if ($model->className() == $relatedModel->className()){
            $disabled[]= $model->pk;
        }

        $validators = [];
        if ($form->hasField($this->name)) {
            $field = $form->getField($this->name);
            $validators = $field->validators;
        }

        if ($this->null === false && $this->autoFetch === false && ($this instanceof BooleanField) === false) {
            $validator = new RequiredValidator;
            $validator->setName($this->name);
            $validator->setModel($this);
            $validators[] = $validator;
        }

        return Creator::createObject(array_merge([
            'class' => $fieldClass,
            'required' => $this->required || !$this->null,
            'form' => $form,
            'choices' => empty($this->choices) ? $choices : $this->choices,
            'name' => $this->name,
            'label' => $this->verboseName,
            'hint' => $this->helpText,
            'value' => $this->getValue(),
            'validators' => array_merge($validators, $this->validators),
            'disabled' =>$disabled,
//            'html' => [
//                'multiple' => $this->value instanceof RelatedManager
//            ]
        ], $extra));
    }
}
