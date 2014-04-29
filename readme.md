# Mindy ORM

Django like ORM implemented in php.
For more code examples see unit tests.

## Fields

@TODO

### IntField

@TODO

### AutoField

@TODO

### BooleanField

@TODO

### CharField

@TODO

### EmailField

@TODO

### DateTimeField

@TODO

### TimeField

@TODO

### FileField

@TODO

### TextField

@TODO

### JsonField

@TODO

## Validators

@TODO


## Sample model

```php
class MyModel extends Model {}
```

Create an empty model with primary key only or create a model with fields:

```php
class MyModel extends Model
{
    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'default' => 'Product',
                'validators' => [
                    function ($value) {
                        if (mb_strlen($value, 'UTF-8') < 3) {
                            return "Minimal length < 3";
                        }

                        return true;
                    },
                ]
            ],
            'price' => ['class' => CharField::className()],
            'description' => ['class' => TextField::className()],
            'category' => [
                'class' => ForeignField::className(),
                'modelClass' => Category::className()
            ],
            'lists' => [
                'class' => ManyToManyField::className(),
                'modelClass' => ProductList::className()
            ]
        ];
    }
}
```

Create table in database:

```php
$sync = new Sync([
    new MyModel()
]);
$sync->create();
```

Delete table from database:

```php
$sync = new Sync([
    new MyModel()
]);
$sync->delete();
```

## Managers

Manager is the interface for current model database.

Every model has default manager named `objects`. You can call it as follows:

```php
User::objects()
```

Manager handles QuerySet. The main method of the manager is getQuerySet().
It returns an object that is afterwards handled by the manager.
If you want to change default Manager logic, you can create your own manager.
For example, ActiveUsersManager():

```php
class ActiveUsersManager(Manager){
    public function getQuerySet(){
        $qs = parent::getQuerySet();
        return $qs->filter(['status' => User::STATUS_ACTIVE]);
    }
}

class User(Model){
    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public function getFields(){
        'name' => [
            'class' => CharField::className(),
            'default' => 'Product',
        ],
        'status' => [
            'class' => IntField::className(),
            'default' => self::STATUS_NOT_ACTIVE
        ]
    }

    public static function activeUsers(){
        $className = get_called_class();
        return new ActiveUsersManager(new $className);
    }
}
```

You can call ActiveUsersManager() using User::activeUsers().
And all the queries will be executed according to your logic handling only the ACTIVE users.
For example, this code helps to select the active users with name starts with 'A':

```php
User::activeUsers()->filter(['name__startswith' => 'A'])->all();
```

## Manager/QuerySet methods

### Methods returning a result

#### Get

Find one object.
If more than 2 objects are found an Exception will be risen. If an object is not found `null` will be return .
If precisely one user is found the object `User` will be returned.

Find one user with pk equals 3.

```php
User::objects()->filter(['pk' => 3])->get();
```

#### All

Returns an array of `Model` objects.
Find all users. Returns array of `User` objects.

```php
User::objects()->all();
```
#### Count

Gets the number of objects in the current Manager/QuerySet.

```php
User::objects()->count();
```

## Methods returning the changed QuerySet

You can chain these methods as follows:

```php
User::objects()->filter(['name' => 'Max'])->exclude(['address' => 'New York'])->order(['-address'])->all();
```

This code finds all users with name 'Max' living not in New York ordered by address in DESC order.

#### Filter

You can find all users in the group which name is `users` using the following code:

```php
User::objects()->filter(['group__name' => 'users'])->all();
```

#### Exclude

You can find all users not in the group which name is `users` using the following code:

```php
User::objects()->exclude(['group__name' => 'users'])->all();
```

#### Order

Finds all users ordered by name in ASC order:

```php
User::objects()->order(['name'])->all();
```

Finds all users ordered by name in DESC order:

```php
User::objects()->order(['-name'])->all();
```

## Lookups

Lookups helps you filtrate QuerySet. Lookups

С помощью лукапов(lookups) вы можете фильтровать QuerySet.
Лукапы применяются в методах менеджера `exclude()` и `filter()` и передаются в них массивом,
где ключом массива являются поля (с лукапами), а значением - значение, по которому и производится фильтрация.
Пример лукапа:

```php
'name__exact'
```

Данный лукап указывает на то, что поле `name` должно быть равно указанному значению. Пример применения:

```php
User::objects()->filter(['name__exact' => 'Max'])->all();
```

Выбираем всех пользователей имя которых равно 'Max'.
*На самом деле, лукап `exact` является лукапом "по умолчанию". То есть, в данном примере можно было было обойтись условием `filter(['name' => 'Max'])`.*


### isnull

Лукап, применяющийся для поиска значений `NULL` в базе данных.
Пример применения:

```php
User::objects()->filter(['name__isnull' => true])->all();
```

Произойдет выбор всех пользователей cо значением имени в БД `NULL`.

### lte, lt

Лукапы, применяющиеся для поиска значений меньше заданного (`lt`), и меньших либо равных заданному (`lte`)
Пример `lt` лукапа:

```php
Product::objects()->filter(['price__lt' => 100.00])->all();
```

Произойдет выбор всех продуктов с ценой строго меньшей `100.00`.

Пример `lte` лукапа:

```php
Product::objects()->filter(['price__lte' => 100.00])->all();
```

Произойдет выбор всех продуктов с ценой меньшей, либо равной `100.00`.

### gte, gt

Лукапы, применяющиеся для поиска значений больше заданного (`gt`), и больших либо равных заданному (`lte`)
Пример `gt` лукапа:

```php
Product::objects()->filter(['price__gt' => 100.00])->all();
```

Произойдет выбор всех продуктов с ценой строго большей `100.00`.

Пример `gte` лукапа:

```php
Product::objects()->filter(['price__gte' => 100.00])->all();
```

Произойдет выбор всех продуктов с ценой большей, либо равной `100.00`.

### exact

Применяется для поиска значний строго равных заданному.
Пример:

```php
User::objects()->filter(['name__exact' => 'Max'])->all();
```

Произойдет выбор всех пользователей с именем 'Max'.
** Лукап является лукапом по умолчанию, то есть в предыдущем примере можно указать просто `['name' => 'Max']` **

### contains

Применяется для поиска значений в которых присутствует заданноу значение (аналог - SQL LIKE).
Регистрозависимый поиск. Для регистронезависимого используйте lookup `icontains`.
Пример:

```php
User::objects()->filter(['name__contains' => 'ax'])->all();
```

Произойдет выбор всех пользователей, в имени которых есть сочетание 'ax'.

### icontains

Полностью повторяет предыдущий lookup, но осуществляет регистронезависимый поиск.

### startswith

Применяется для поиска значений, начинающихся с заданного значения:
Регистрозависимый. Для регистронезависимого используйте lookup `istartswith`.
Пример:

```php
User::objects()->filter(['name__startswith' => 'M'])->all();
```

Произойдет выбор всех пользователей, имя которых начинается с 'M'.

### istartswith

Полностью повторяет предыдущий lookup, но осуществляет регистронезависимый поиск.

### endswith

Применяется для поиска значний, заканчивающихся заданным значением:
Регистрозависимый. Для регистронезависимого используйте lookup `iendswith`.
Пример:

```php
User::objects()->filter(['name__endswith' => 'on'])->all();
```

Произойдет выбор всех пользователей, имя которых начинается с 'on'.

### iendswith

Полностью повторяет предыдущий lookup, но осуществляет регистронезависимый поиск.

### in

Применяется для поиска значений, попадающих в список переданных значений:
Пример:

```php
User::objects()->filter(['pk__in' => [1, 2]])->all();
```
Произойдет выбор всех пользователей, pk которых попадают в список `[1, 2]`.

#### This lookup can take QuerySet object

Данный лукап позволяет принимать не только массив значений, но и объект QuerySet. В этом случае в запрос будет добавлен подзапрос.
Пример:

```
$group_qs = Group::objects()->filter(['name__startswith' => 'A']);
$users = User::objects()->filter(['groups__pk__in' => $group_qs->select('id') ])->all();
```

Произойдет выбор всех пользователей, имена групп которых начинаются с 'A'.
Будет выполнен всего один запрос на выбор пользователей.

### range

Данный лукап позволяет найти значения, расположенные между переданных значений.
Данный лукап принимает массив из двух элементов.

Пример:

```php
Product::objects()->filter(['price__range' => [10, 20]])
```

Произойдет выбор всех продуктов с ценой от 10 до 20.

@TODO

### year

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном году.

Пример:

```php
Product::objects()->filter(['date_added__year' => 2014])
```

Произойдет выбор всех продуктов, добавленных в 2014 году.

### month

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном месяце.

Пример:

```php
Product::objects()->filter(['date_added__year' => 12])
```

Произойдет выбор всех продуктов, добавленных в декабре.

### day

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном дне месяца.

Пример:

```php
Product::objects()->filter(['date_added__day' => 25])
```

Произойдет выбор всех продуктов, добавленных 25 числа любого месяца.

### week_day

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном дне недели.
Значения: 1 - Воскресенье, 2 - Понедельник, ..., 7 - Суббота
Пример:

```php
Product::objects()->filter(['date_added__week_day' => 2])
```
** Notice! На текущий момент реализовано только для MySQL **

Произойдет выбор всех продуктов, добавленных в понедельник.

### hour

Данный лукап работает только с полями типа `DateTimeField`, `TimeField`.
Позволяет найти все значения, расположенные в заданном часе.
Пример:

```php
Product::objects()->filter(['date_added__hour' => 10])
```

Произойдет выбор всех продуктов, добавленных в 10 часов.

### minute

Данный лукап работает только с полями типа `DateTimeField`, `TimeField`.
Позволяет найти все значения, расположенные в заданной минуте.
Пример:

```php
Product::objects()->filter(['date_added__minute' => 35])
```

Произойдет выбор всех продуктов, добавленных в 35 минут.

### second

Данный лукап работает только с полями типа `DateTimeField`, `TimeField`.
Позволяет найти все значения, расположенные в заданной секунде.
Пример:

```php
Product::objects()->filter(['date_added__minute' => 45])
```

Произойдет выбор всех продуктов, добавленных в 45 секунд.

### search

** Не реализовано **

### regex

Поиск по регулярному выражению.
Регистрозависимый. Для регистронезависимого используйте lookup `iregex`.
Пример:

```php
Product::objects()->filter(['name__regex' => '[a-z]'])
```

Произойдет выбор всех продуктов, соответствующих регулярному выражению `[a-z]`

** Notice! На текущий момент реализовано только для MySQL **

### iregex

Полностью повторяет предыдущий lookup, но осуществляет регистронезависимый поиск.
** Notice! На текущий момент реализовано только для MySQL **

## Relations

@TODO: list of all relations

### ForeignField

@TODO

### HasManyField

@TODO

### ManyToManyField

@TODO

#### With through model

@TODO

### Lookups with relations

@TODO

## Aggregations

### max

@TODO

### min

@TODO

### avg

@TODO

### sum

@TODO