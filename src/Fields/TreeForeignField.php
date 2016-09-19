<?php

namespace Mindy\Orm\Fields;

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
                $list[$model->pk] = $level ? str_repeat("—", $level) . ' ' . $model->name : $model->name;
            }
            return $list;
        };

        if ($this->primary || $this->editable === false) {
            return null;
        }

        if ($fieldClass === null) {
            $fieldClass = $this->choices ? \Mindy\Form\Fields\SelectField::class : \Mindy\Form\Fields\TextField::class;
        } elseif ($fieldClass === false) {
            return null;
        }

        $model = $this->getModel();
        $disabled = [];
        if ($model->className() == $relatedModel->className() && $relatedModel->getIsNewRecord() === false) {
            $disabled[] = $model->pk;
        }

        return parent::getFormField($form, $fieldClass, array_merge([
            'disabled' => $disabled,
            'choices' => empty($this->choices) ? $choices : $this->choices
        ], $extra));
    }
}
