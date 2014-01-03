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
 * @date 03/01/14.01.2014 22:03
 */

namespace Mindy\Db\Fields;


use Exception;

class ManyToManyField extends RelatedField
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        /* @var \Mindy\Db\Orm $owner */
        $owner = $options['owner'];
        $ownerClass = $owner->className();
        $modelClass = $options['model'];
        $link = ['id' => strtolower($ownerClass) . '_id'];

        /* @var \Mindy\Db\OrmRelation $relation */
        $relation = $this->hasMany($owner, $modelClass, $link);

        if(isset($options['through'])) {
            $relation->via($options['through']);
        } else {
            $relation->viaTable(strtolower($ownerClass . '_' . $modelClass), [strtolower($modelClass) . '_id' => 'id']);
        }

        if(!$relation->multiple) {
            throw new Exception("Incorrect relation");
        } else {
            $this->setRelation($relation);
        }
    }

    public function sqlType()
    {
        return false;
    }
}
