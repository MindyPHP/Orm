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

@TODO

### isnull

@TODO

### lte, lt

@TODO

### gte, gt

@TODO

### exact

@TODO

### contains

@TODO

### icontains

@TODO

### startswith

@TODO

### istartswith

@TODO

### endswith

@TODO

### iendswith

@TODO

### in

@TODO

#### This lookup can take QuerySet object

@TODO

### range

@TODO

### year

@TODO

### month

@TODO

### date

@TODO

### week_day

@TODO

### hour

@TODO

### minute

@TODO

### second

@TODO

### search

@TODO

### regex

@TODO

### iregex

@TODO

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