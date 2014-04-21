# Mindy ORM

Django based ORM implemented in php.


## Example model

```php
class MyModel extends Model {}
```

Create empty model with primary key only or create model with fields:

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

## Methods

### Get

Find user. If returned user > 2 raise Exception. If user not found return `null`.

```php
User::objects()->filter(['pk__gte' => 3, 'is_active__isnull' => false])->get();
```

### Filter

Find all users in group `users`:

```php
User::objects()->filter(['group__name' => 'users'])->all();
```

### Exclude

Find all users not in group `users`

```php
User::objects()->exclude(['group__name' => 'users'])->all();
```

### Order

Find all users order by name DESC

```php
User::objects()->order(['-name'])->all();
```
