<?php

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;
use Mindy\Orm\Sync;
use Mindy\Query\Connection;

require_once __DIR__ . '/bootstrap.php';

Model::setConnection(new Connection([
    'dsn' => 'sqlite::memory:'
]));

class Page extends Model
{
    public function getFields()
    {
        return [
            'name' => ['class' => CharField::className()],
            'slug' => ['class' => CharField::className()],
            'content' => ['class' => TextField::className()],
            'created_at' => ['class' => IntField::className()]
        ];
    }
}

function formatSize($size)
{
    $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return $size ? round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
}

function main($params)
{
    $test = array_shift($params);

    $sync = new Sync([new Page]);
    $sync->create();

    switch ($test) {
        case 'typical_page':
            $data = [
                ['name' => 1, 'slug' => 1, 'content' => 1, 'created_at' => 1],
                ['name' => 2, 'slug' => 2, 'content' => 2, 'created_at' => 2],
                ['name' => 3, 'slug' => 3, 'content' => 3, 'created_at' => 3],
                ['name' => 4, 'slug' => 4, 'content' => 4, 'created_at' => 4],
            ];
            foreach($data as $item) {
                Page::objects()->getOrCreate($item);
            }

            $memory = [];
            echo "->all() as objects: ";
            foreach (range(1, 1000) as $i) {
                Page::objects()->all();
                $memory[] = formatSize(memory_get_peak_usage());
                gc_collect_cycles();
            }
            echo max($memory) . PHP_EOL;

            $memory = [];
            echo "->asArray()->all() as array: ";
            foreach (range(1, 1000) as $i) {
                Page::objects()->asArray()->all();
                $memory[] = formatSize(memory_get_peak_usage());
                gc_collect_cycles();
            }
            echo max($memory) . PHP_EOL;

            break;
        case '1000':
            foreach(range(1, 1000) as $i) {
                Page::objects()->getOrCreate(['name' => $i, 'slug' => $i, 'content' => $i, 'created_at' => $i]);
            }

            echo "Total models: " . Page::objects()->count() . PHP_EOL;
            gc_collect_cycles();
            Page::objects()->all();
            echo formatSize(memory_get_peak_usage()) . PHP_EOL;
        default:
            break;
    }

    $sync->delete();
    return 0;
}


$params = $argv;
unset($params[0]);
exit(main($params));
