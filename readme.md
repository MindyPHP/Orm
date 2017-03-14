# Mindy ORM (Django like ORM)

[![Build Status](https://travis-ci.org/MindyPHP/Orm.svg?branch=master)](https://travis-ci.org/MindyPHP/Orm)
[![Coverage Status](https://img.shields.io/coveralls/studio107/Mindy_Orm.svg)](https://coveralls.io/r/studio107/Mindy_Orm)
[![Latest Stable Version](https://poser.pugx.org/mindy/orm/v/stable.svg)](https://packagist.org/packages/mindy/orm)
[![Total Downloads](https://poser.pugx.org/mindy/orm/downloads.svg)](https://packagist.org/packages/mindy/orm)

## Компоненты

[Migrations](https://github.com/MindyPHP/Mindy_Orm_Migrations)

***Еще одна*** реализация Django ORM на PHP. На текущий момент находится в стадии активной доработки и не
рекомендуется к использованию в `production` до версии ***1.0***.

Приносим извинения за неполноту документации. Документация находится разработке.

Часть кода основана на yii2 фреймворке, но ввиду монолитности ядра мы были вынуждены
"распилить" фреймворк по отдельным пакетам. В `Mindy ORM` активно используется Query из yii2.

Поддерживаемые типы субд: `sqlite`, `mysql`, `pgsql`, `mssql` (все то, что умеет yii2 query).
Теоретически ORM способна работать с `NoSql` хранилищами за счет реализации собственного менеджера
с переопределением `Lookup`'ов.


## Краткий пример возможностей ORM

Опишем подключение к бд:

```php
Model::setConnection([
    'sqlite::memory'
]);
```

Опишем модель:

```php
class MyModel extends Model
{
    public function getFields()
    {
        return [
            // Ключ - название поля при обращении к модели
            'name' => [
                // Тип поля
                'class' => CharField::class,
                // Длинна поля
                'length' => 100,
                // NULL|NOT NULL
                'null' => false,
                // "Читабельное" имя модели. Используется в Mindy\Form\ModelForm
                // для построения форм автоматически.
                'verboseName' => 'Продукция',
                // Пример валидаторов
                'validators' => [
                    function ($value) {
                        if (mb_strlen($value, 'UTF-8') < 3) {
                            return "Minimal length < 3";
                        }

                        return true;
                    },
                    new UniqueValidator
                ]
            ],
            'price' => [
                'class' => CharField::class,
                // Значение по умолчанию
                'default' => 0
            ],
            'description' => [
                'class' => TextField::class,
                'default' => ''
            ],
            'category' => [
                'class' => ForeignField::class,
                'modelClass' => Category::class,
                'null' => true
            ],
            'lists' => [
                'class' => ManyToManyField::class,
                'modelClass' => ProductList::class
            ]
        ];
    }
}
```

Описание модели завершено. Валидация происходит на основе валидации полей модели.
Модель описана, теперь необходимо создать ее в субд. Для этого выполним следующий код:

```php
$sync = new Sync([
    new MyModel()
]);
$sync->create();
```

В бд создатутся таблицы всех переданных моделей а так же индексы и связи если это возможно.

Создадим несколько записей:

```php
// Если модель с идентичными полями найдется, то ORM вернет ее, иначе создаст.
$model = MyModel::objects()->getOrCreate(['name' => 'Поросенок петр', 'price' => 1]);

$modelTwo = new MyModel;
// Массовое присвоение аттрибутов
$modelTwo->setData([
    'name' => 'Рубаха',
    'price' => 2
]);
// Валидация и сохранение
if($modelTwo->isValid() && $modelTwo->save()) {
    echo 'Модель сохранена';
}

$modelThree = new MyModel;
$modelThree->name = 'Джинсы';
$modelThree->price = 3;
// Валидация и сохранение
if($modelThree->isValid() && $modelThree->save()) {
    echo 'Модель сохранена';
}
```

Выборки реализованы по аналогии с Django Framework:

```php
// SELECT * FROM my_model WHERE price >= 2
$models = MyModel::objects()->filter(['price__gte' => 2])->all();

// SELECT * FROM my_model WHERE name = 'Рубаха'
$models = MyModel::objects()->filter(['name' => 'Рубаха'])->all();

// SELECT * FROM my_model WHERE id IN (1, 2, 3)
$models = MyModel::objects()->filter(['pk__in' => [1, 2, 3]])->all();
```

И так далее. Более подробную информацию
смотрите в разделе ***Lookups***

Очистим базу данных:

```php
$sync = new Sync([
    new MyModel()
]);
$sync->delete();
```

## Менеджеры

Менеджер это интерфейс проксирующий до класса `QuerySet`, который занимается обработкой
наших `Lookup`'ов и выполнением запросов. Каждая модель по умолчанию имеет менеджер `objects()`.

```php
User::objects()
```

Менеджер обрабатывает QuerySet. Основным методом менеджера является `getQuerySet()`.
Это возвращает объект, который впоследствии обрабатывается менеджером.
Если вы хотите изменить логику менеджера по умолчанию, вы можете создать свой собственный менеджер.
Например, `activeUsersManager()`:

```php
class ActiveUsersManager extends Manager
{
    public function getQuerySet()
    {
        $qs = parent::getQuerySet();
        return $qs->filter(['status' => User::STATUS_ACTIVE]);
    }
}
```

Модель с собственным менеджером:

```php
class User extends Model
{
    public function getFields()
    {
        'name' => [
            'class' => CharField::class,
        ],
        'status' => [
            'class' => BooleanField::class,
            'default' => false
        ]
    }

    public static function activeUsersManager($instance = null)
    {
        $className = get_called_class();
        return new ActiveUsersManager($instance ? $instance : new $className);
    }
}
```

Теперь вы можете использовать `ActiveUsersManager()` с помощью `User::activeUsers()`.
И все запросы будут выполняться в соответствии с вашей логикой обработки только активных пользователей.
Например, этот код поможет выбрать активных пользователей имя которых начинается с 'A':

```php
User::activeUsers()->filter(['name__startswith' => 'A'])->all();
```

## Manager/QuerySet методы

### Methods returning a result

#### Get

Выборка одного объекта. В случае если объектов соответствующих условию больше 1,
выбрасываем исключение, иначе возвращаем объект. Если объектов не найдено, то возвращаем null.

Найдем объект где `pk == 5`.

```php
$model = User::objects()->filter(['pk' => 5])->get();
```

#### All

Возвращает массив моделей класса `Model` или ассоциативный массив в случае если
вызван метод `asArray()`.

Выборка всех пользователей. Вернется массив моделей класса `User`.

```php
User::objects()->all();
```

Выборка всех пользователей. Вернется ассоциативный массив.

```php
User::objects()->asArray()->all();
```

#### Count

Возвращает число объектов подходящих под условия выборки

```php
User::objects()->count();
```

## Методы, возвращающие измененный QuerySet

Вы можете вызывать эти методы последовательно:

```php
User::objects()->filter(['name' => 'Max'])->exclude(['address' => 'New York'])->order(['-address'])->all();
```

Данный код возвращает всех пользователей с именем `Max` живущих не в `New York` с сортировкой
по адресу в обратном порядке.

```sql
SELECT * FROM user WHERE name='Max' AND address != 'New York' ORDER BY address DESC
```

#### Filter

Вы можете найти всех пользователей состоящих в группе с именем `example`, используя следующий код:

```php
User::objects()->filter(['group__name' => 'example'])->all();
```

`group` в данном случае связь `m2m` до модели `Group`

#### Exclude

Вы можете найти всех пользователей которые не состоят в группе с названием `example`, используя следующий код:

```php
User::objects()->exclude(['group__name' => 'users'])->all();
```

`group` в данном случае связь `m2m` до модели `Group`

#### Order

Поиск всех пользователей и сортировка по имени:

```php
User::objects()->order(['name'])->all();
```

Поиск всех пользователей и сортировка по имени в обратном порядке:

```php
User::objects()->order(['-name'])->all();
```

Поиск всех пользователей и сортировка в случайном порядке

```php
User::objects()->order(['?'])->all();
```

## Lookups

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

```sql
SELECT * FROM user WHERE name = 'Max'
```

### isnull

Лукап, применяющийся для поиска значений `NULL` в базе данных.
Пример применения:

```php
User::objects()->filter(['name__isnull' => true])->all();
```

Произойдет выбор всех пользователей cо значением имени в БД `NULL`.

```sql
SELECT * FROM user WHERE name IS NULL
```

Если передать `false` в качестве значения то sql запрос будет выглядеть слудющим
образом:

```sql
SELECT * FROM user WHERE name IS NOT NULL
```

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

```php
$dateTime = new DateTime();
$dateTime->setTimestamp(strtotime('+15 days'))
Product::objects()->filter(['created_at__lte' => $dateTime])->all();
```

Произойдет выбор всех продуктов с ценой меньшей, либо равной `100.00`.

`lt` формирует слудющий sql:

```sql
SELECT * FROM user WHERE price < 100.00
```

`lte` формирует слудющий sql:

```sql
SELECT * FROM user WHERE price <= 100.00
```

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

`gt` формирует слудющий sql:

```sql
SELECT * FROM user WHERE price > 100.00
```

`gte` формирует слудющий sql:

```sql
SELECT * FROM user WHERE price >= 100.00
```

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
***Регистрозависимый*** поиск. Для регистронезависимого используйте lookup `icontains`.
Пример:

```php
User::objects()->filter(['name__contains' => 'ax'])->all();
```

Произойдет выбор всех пользователей, в имени которых есть сочетание 'ax'.

```sql
SELECT * FROM user WHERE name LIKE '%ax%'
```

### icontains

Полностью повторяет lookup `contains`, но осуществляет ***регистронезависимый*** поиск.

### startswith

Применяется для поиска значений, начинающихся с заданного значения:
Регистрозависимый. Для регистронезависимого используйте lookup `istartswith`.
Пример:

```php
User::objects()->filter(['name__startswith' => 'M'])->all();
```

Произойдет выбор всех пользователей, имя которых начинается с 'M'.

```sql
SELECT * FROM user WHERE name LIKE 'M%'
```

### istartswith

Полностью повторяет lookup `startswith`, но осуществляет регистронезависимый поиск.

### endswith

Применяется для поиска значний, заканчивающихся заданным значением:
Регистрозависимый. Для регистронезависимого используйте lookup `iendswith`.
Пример:

```php
User::objects()->filter(['name__endswith' => 'on'])->all();
```

Произойдет выбор всех пользователей, имя которых начинается с 'on'.

```sql
SELECT * FROM user WHERE name LIKE '%on'
```

### iendswith

Полностью повторяет lookup `iendswith`, но осуществляет регистронезависимый поиск.

### in

Применяется для поиска значений, попадающих в список переданных значений:
Пример:

```php
User::objects()->filter(['pk__in' => [1, 2]])->all();
```
Произойдет выбор всех пользователей, pk которых попадают в список `[1, 2]`.

```sql
SELECT * FROM user WHERE id IN (1, 2)
```

***Lookup может принимать QuerySet в качестве значения***

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

```sql
SELECT * FROM product WHERE price BETWEEN 10 AND 20
```

### year

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном году.

Пример:

```php
Product::objects()->filter(['date_added__year' => 2014])
```

Произойдет выбор всех продуктов, добавленных в 2014 году.

***Внимание:*** sql запрос будет отличаться для разных субд.

### month

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном месяце.

Пример:

```php
Product::objects()->filter(['date_added__year' => 12])
```

Произойдет выбор всех продуктов, добавленных в декабре.

***Внимание:*** sql запрос будет отличаться для разных субд.

### day

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном дне месяца.

Пример:

```php
Product::objects()->filter(['date_added__day' => 25])
```

Произойдет выбор всех продуктов, добавленных 25 числа любого месяца.

***Внимание:*** sql запрос будет отличаться для разных субд.

### week_day

Данный лукап работает только с полями типа `DateTimeField`, `DateField`.
Позволяет найти все значения, расположенные в заданном дне недели.

Значения: 1 - Воскресенье, 2 - Понедельник, ..., 7 - Суббота.
Порядок дней определяет ORM и подстраивает под текущую субд по следующей причине:

```
Method                              Range
------                              -----
PYTHON
    datetime_object.weekday()       0-6    Sunday=6
    datetime_object.isoweekday()    1-7    Sunday=7
    dt_object.isoweekday() % 7      0-6    Sunday=0 # Can easily add 1 for a 1-7 week where Sunday=1

MYSQL
    DAYOFWEEK(timestamp)            1-7    Sunday=1
    WEEKDAY(timestamp)              0-6    Monday=0

POSTGRES
    EXTRACT('dow' FROM timestamp)   0-6    Sunday=0
    TO_CHAR(timestamp, 'D')         1-7    Sunday=1

ORACLE
    TO_CHAR(timestamp, 'D')         1-7    Sunday=1 (US), Sunday=6 (UK)
```

Пример:

```php
Product::objects()->filter(['date_added__week_day' => 2])
```

Произойдет выбор всех продуктов, добавленных в понедельник.

***Внимание:*** sql запрос будет отличаться для разных субд.

### hour

Данный лукап работает только с полями типа `DateTimeField`, `TimeField`.
Позволяет найти все значения, расположенные в заданном часе.
Пример:

```php
Product::objects()->filter(['date_added__hour' => 10])
```

Произойдет выбор всех продуктов, добавленных в 10 часов.

***Внимание:*** sql запрос будет отличаться для разных субд.

### minute

Данный лукап работает только с полями типа `DateTimeField`, `TimeField`.
Позволяет найти все значения, расположенные в заданной минуте.
Пример:

```php
Product::objects()->filter(['date_added__minute' => 35])
```

Произойдет выбор всех продуктов, добавленных в 35 минут.

***Внимание:*** sql запрос будет отличаться для разных субд.

### second

Данный лукап работает только с полями типа `DateTimeField`, `TimeField`.
Позволяет найти все значения, расположенные в заданной секунде.
Пример:

```php
Product::objects()->filter(['date_added__minute' => 45])
```

Произойдет выбор всех продуктов, добавленных в 45 секунд.

***Внимание:*** sql запрос будет отличаться для разных субд.

### search

** Не реализовано **

### regex

Поиск по регулярному выражению.
***Регистрозависимый***. Для регистронезависимого используйте lookup `iregex`.
Пример:

```php
Product::objects()->filter(['name__regex' => '[a-z]'])
```

Произойдет выбор всех продуктов, соответствующих регулярному выражению `[a-z]`

***Внимание:*** sql запрос будет отличаться для разных субд.

### iregex

Полностью повторяет предыдущий lookup, но осуществляет регистронезависимый поиск.

***Внимание:*** sql запрос будет отличаться для разных субд.

## Q-объекты (Query-объекты)

Q-объекты необходимы для удобного формирования условий выборки. Существует 2 Q-объекта: **OrQ** и **AndQ**.

Q-объект формируется следующим образом:

```php
new OrQ([['status' => 1, 'user_id' => 1],['status' => 2, 'user_id' => 4]]);
```

И затем его можно передать в методы **filter** или **exclude**:

```php
Requests::objects()->filter([new OrQ([['status' => 1, 'user_id' => 1],['status' => 2, 'user_id' => 4]])])->all()
```

Данный запрос выберет нам все заявки со статусом 1 от пользователя с **id** равным 1 и заявки со статусом 2 от пользователя с **id** равным 4.
