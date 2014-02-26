<?php
include __DIR__ . '/src/Mindy/Orm/Validator/Validator.php';
include __DIR__ . '/src/Mindy/Orm/Validator/MinLengthValidator.php';
include __DIR__ . '/src/Mindy/Orm/Validator/EmailValidator.php';

include __DIR__ . '/src/Mindy/Orm/Base.php';
include __DIR__ . '/src/Mindy/Orm/Orm.php';
include __DIR__ . '/src/Mindy/Orm/Model.php';
include __DIR__ . '/src/Mindy/Orm/Manager.php';
include __DIR__ . '/src/Mindy/Orm/Relation.php';

include __DIR__ . '/src/Mindy/Orm/Fields/Field.php';
include __DIR__ . '/src/Mindy/Orm/Fields/IntField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/AutoField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/CharField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/BooleanField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/TextField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/JsonField.php';

include __DIR__ . '/src/Mindy/Orm/Fields/RelatedField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/ForeignField.php';
include __DIR__ . '/src/Mindy/Orm/Fields/ManyToManyField.php';

function d() {
    $debug = debug_backtrace();
    $args = func_get_args();
    $data = array(
        'data' => $args,
        'debug' => array(
            'file' => $debug[0]['file'],
            'line' => $debug[0]['line'],
        )
    );
    if(class_exists('Mindy\Helper\Dumper')) {
        Mindy\Helper\Dumper::dump($data, 10);
    } else {
        var_dump($data);
    }
    die();
}
