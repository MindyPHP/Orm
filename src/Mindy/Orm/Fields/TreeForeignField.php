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
 * @date 03/11/14.11.2014 18:01
 */

namespace Mindy\Orm\Fields;

use Mindy\Helper\Creator;
use Mindy\Validation\RequiredValidator;

class TreeForeignField extends ForeignField
{
    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DropDownField')
    {
        $model = $this->getModel();
        $choices = function () use ($model) {
            $list = ['' => ''];

            $qs = $model->objects()->order(['root', 'lft']);
            if ($model->getIsNewRecord()) {
                $parents = $qs->all();
            } else {
                $parents = $qs->exclude(['pk' => $model->pk])->all();
            }
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

        return Creator::createObject([
            'class' => $fieldClass,
            'required' => $this->required || !$this->null,
            'form' => $form,
            'choices' => empty($this->choices) ? $choices : $this->choices,
            'name' => $this->name,
            'label' => $this->verboseName,
            'hint' => $this->helpText,
            'validators' => array_merge($validators, $this->validators)

//            'html' => [
//                'multiple' => $this->value instanceof RelatedManager
//            ]
        ]);
    }
}
