<?php

// yii2

include __DIR__ . '/vendor/yii2/framework/yii/Yii.php';

include __DIR__ . '/vendor/yii2/framework/yii/base/Arrayable.php';
include __DIR__ . '/vendor/yii2/framework/yii/base/Object.php';
include __DIR__ . '/vendor/yii2/framework/yii/base/Component.php';

include __DIR__ . '/vendor/yii2/framework/yii/db/Command.php';
include __DIR__ . '/vendor/yii2/framework/yii/db/Connection.php';

include __DIR__ . '/vendor/yii2/framework/yii/log/Logger.php';
include __DIR__ . '/vendor/yii2/framework/yii/log/Target.php';
include __DIR__ . '/vendor/yii2/framework/yii/log/FileTarget.php';

class Application
{
    public function getLog()
    {
        return new yii\log\Logger([
            'targets' => [
                 'file' => [
                     'class' => 'yii\log\FileTarget',
                     'levels' => ['trace', 'info'],
                     'categories' => ['yii\*'],
                 ],
             ],
        ]);
    }

    public function getRuntimePath()
    {
        return __DIR__ . '/runtime';
    }
}

Yii::$app = new Application();

// Mindy

include __DIR__ . '/src/Mindy/Db/Traits/Migrations.php';
include __DIR__ . '/src/Mindy/Db/Traits/Fields.php';

include __DIR__ . '/src/Mindy/Db/Validator/Validator.php';
include __DIR__ . '/src/Mindy/Db/Validator/MinLengthValidator.php';
include __DIR__ . '/src/Mindy/Db/Validator/EmailValidator.php';

include __DIR__ . '/src/Mindy/Db/Connection.php';
include __DIR__ . '/src/Mindy/Db/OrmBase.php';
include __DIR__ . '/src/Mindy/Db/Orm.php';
include __DIR__ . '/src/Mindy/Db/Model.php';

include __DIR__ . '/src/Mindy/Db/Fields/Field.php';
include __DIR__ . '/src/Mindy/Db/Fields/IntField.php';
include __DIR__ . '/src/Mindy/Db/Fields/AutoField.php';
include __DIR__ . '/src/Mindy/Db/Fields/CharField.php';
include __DIR__ . '/src/Mindy/Db/Fields/TextField.php';
include __DIR__ . '/src/Mindy/Db/Fields/JsonField.php';
