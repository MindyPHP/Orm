<?php

namespace Mindy\Orm\Fields;

/**
 * Class TreeForeignField
 * @package Mindy\Orm
 */
class TreeForeignField extends ForeignField
{
    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\SelectField')
    {
        if ($this->primary || $this->editable === false) {
            return null;
        }

        $relatedModel = $this->getRelatedModel();

        if (!empty($this->choices)) {
            $choices = $this->choices;
        } else {
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
        }

        $model = $this->getModel();
        $disabled = [];
        if (get_class($model) == get_class($relatedModel) && $relatedModel->getIsNewRecord() === false) {
            $disabled[] = $model->pk;
        }

        $value = $model->parent_id;
        if (empty($value)) {
            $value = $this->default ? $this->default : null;
        }

        return [
            'disabled' => $disabled,
            'choices' => $choices,
            'class' => $fieldClass,
            'required' => $this->isRequired(),
            'name' => $this->name,
            'label' => $this->verboseName,
            'hint' => $this->helpText,
            'value' => $value
        ];
    }
}
