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
 * @date 03/01/14.01.2014 22:02
 */

namespace Mindy\Db\Fields;


use Mindy\Db\Orm;

abstract class RelatedField extends IntField
{
    /**
     * @var OrmRelation
     */
    private $_relation;

    /**
     * @var string
     */
    public $relatedName;

    public function getRelation()
    {
        return $this->_relation;
    }

    public function setRelation(OrmRelation $relation)
    {
        return $this->_relation = $relation;
    }

    /**
     * Creates an [[ActiveRelation]] instance.
     * This method is called by [[hasOne()]] and [[hasMany()]] to create a relation instance.
     * You may override this method to return a customized relation.
     * @param array $config the configuration passed to the ActiveRelation class.
     * @return OrmRelation the newly created [[ActiveRelation]] instance.
     */
    public function createRelation(array $config)
    {
        return new OrmRelation($config);
    }

    /**
     * Declares a `has-one` relation.
     * The declaration is returned in terms of an [[ActiveRelation]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-one` relation means that there is at most one related record matching
     * the criteria set by this relation, e.g., a customer has one country.
     *
     * For example, to declare the `country` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ~~~
     * public function getCountry()
     * {
     *     return $this->hasOne(Country::className(), ['id' => 'country_id']);
     * }
     * ~~~
     *
     * Note that in the above, the 'id' key in the `$link` parameter refers to an attribute name
     * in the related class `Country`, while the 'country_id' value refers to an attribute name
     * in the current AR class.
     *
     * Call methods declared in [[ActiveRelation]] to further customize the relation.
     *
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return ActiveRelationInterface the relation object.
     */
    public function hasOne(Orm $model, $class, $link)
    {
        return $this->createRelation([
            'modelClass' => $class,
            'primaryModel' => $model,
            'link' => $link,
            'multiple' => false,
        ]);
    }

    /**
     * Declares a `has-many` relation.
     * The declaration is returned in terms of an [[ActiveRelation]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-many` relation means that there are multiple related records matching
     * the criteria set by this relation, e.g., a customer has many orders.
     *
     * For example, to declare the `orders` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ~~~
     * public function getOrders()
     * {
     *     return $this->hasMany(Order::className(), ['customer_id' => 'id']);
     * }
     * ~~~
     *
     * Note that in the above, the 'customer_id' key in the `$link` parameter refers to
     * an attribute name in the related class `Order`, while the 'id' value refers to
     * an attribute name in the current AR class.
     *
     * @param string $class the class name of the related record
     * @param array $link the primary-foreign key constraint. The keys of the array refer to
     * the attributes of the record associated with the `$class` model, while the values of the
     * array refer to the corresponding attributes in **this** AR class.
     * @return ActiveRelationInterface the relation object.
     */
    public function hasMany(Orm $model, $class, $link)
    {
        return $this->createRelation([
            'modelClass' => $class,
            'primaryModel' => $model,
            'link' => $link,
            'multiple' => true,
        ]);
    }
}
