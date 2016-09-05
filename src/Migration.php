<?php

namespace Mindy\Orm;

use Exception;
use function Mindy\app;
use Mindy\Helper\Json;
use Mindy\Helper\Traits\Accessors;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;

/**
 * Class Migration
 * @package Mindy\Orm
 */
class Migration
{
    use Accessors;

    /**
     * @var Model
     */
    private $_model;
    /**
     * @var string path to migrations directory
     */
    private $_path;
    /**
     * @var string
     */
    private $_name;
    /**
     * @var string used for unit testing only
     */
    private $_tmpName;
    /**
     * @var string database name (key in array databases) from settings.php. Example: default
     */
    private $_db;
    /**
     * @var string
     */
    protected $space = '        ';

    public function __construct(Model $model, $path)
    {
        $this->_model = $model;
        if (!is_dir($path)) {
            throw new Exception("Directory $path not found");
        }
        $this->_path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public function setDb($db)
    {
        $this->_db = $db;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function generateName()
    {
        if ($this->_tmpName !== null) {
            $name = $this->_tmpName;
        } else {
            $name = $this->_model->classNameShort();
        }

        if ($this->_name === null) {
            $this->_name = $name . '_' . time();
        }

        return $this->_name . '.json';
    }

    public function getFields()
    {
        $fields = [];
        $modelFields = $this->_model->getFieldsInit();
        foreach ($modelFields as $name => $field) {
            if ($field->sqlType() !== false) {
                if ($field instanceof ForeignField) {
                    $name .= "_id";
                }
            }
            $fields[$name] = array_merge($field->getOptions(), [
                'hash' => $field->hash()
            ]);
        }
        return $fields;
    }

    /**
     * @return string json encoded field options
     */
    public function exportFields()
    {
        return Json::encode($this->getFields(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function getMigrations()
    {
        if ($this->_tmpName !== null) {
            $name = $this->_tmpName;
        } else {
            $name = $this->_model->classNameShort();
        }
        return glob($this->_path . '/' . $name . '_*.json');
    }

    protected function getLastMigration()
    {
        $files = $this->getMigrations();
        $migrations = [];
        foreach ($files as $file) {
            list($name, $timestamp) = explode('_', basename($file));
            if (!isset($migrations[$name])) {
                $migrations[$name] = [];
            }
            $migrations[$name][] = $timestamp;
        }

        foreach ($migrations as $model => $timestamps) {
            asort($timestamps);
            $lastTimestamp = $timestamps[count($timestamps) - 1];
            return $this->readMigration($this->_path . DIRECTORY_SEPARATOR . $model . '_' . $lastTimestamp);
        }

        return [];
    }

    public function hasChanges()
    {
        $files = $this->getMigrations();
        if (count($files) === 0) {
            return true;
        }

        $migrations = [];
        foreach ($files as $file) {
            list($name, $timestamp) = explode('_', basename($file));
            if (!isset($migrations[$name])) {
                $migrations[$name] = [];
            }
            $migrations[$name][] = $timestamp;
        }

        foreach ($migrations as $model => $timestamps) {
            asort($timestamps);
            $lastTimestamp = $timestamps[count($timestamps) - 1];

            $lastFields = $this->readMigration($this->_path . DIRECTORY_SEPARATOR . $model . '_' . $lastTimestamp);
            $currentFields = $this->getFields();
            if (count($lastFields) != count($currentFields)) {
                return true;
            }

            foreach ($lastFields as $name => $field) {
                if (array_key_exists($name, $currentFields)) {
                    if ($field['hash'] != $currentFields[$name]['hash']) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    protected function readMigration($filename)
    {
        $json = file_get_contents($filename);
        return Json::decode($json);
    }

    public function save()
    {
        if ($this->hasChanges()) {
            $path = $this->_path . DIRECTORY_SEPARATOR . $this->generateName();
            if (file_exists($path)) {
                throw new Exception("File $path exists");
            }
            if (file_put_contents($path, $this->exportFields()) === false) {
                throw new Exception("Failed to save migration");
            }
            sleep(1);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Used for unit testing only
     * @param $name
     */
    public function setName($name)
    {
        $this->_tmpName = $name;
    }

    public function getSafeUp()
    {
        $lines = [];
        $added = [];
        $deleted = [];
        $fields = $this->getFields();
        $lastMigrationFields = $this->getLastMigration();

        foreach ($fields as $name => $field) {
            if (array_key_exists($name, $lastMigrationFields) === false) {
                $added[$name] = $field;
            }
        }

        if (!empty($lastMigrationFields)) {
            foreach ($lastMigrationFields as $name => $field) {
                if (array_key_exists($name, $fields) === false) {
                    $deleted[$name] = $field;
                    continue;
                }

                if ($field['hash'] == $fields[$name]['hash']) {
                    continue;
                }

                if ($field['sqlType'] != $fields[$name]['sqlType']) {
                    $lines[] = $this->space . '$this->alterColumn("' . $this->_model->tableName() . '", "' . $name . '", "' . $fields[$name]['sqlType'] . '");';
                } elseif ($field['sqlType'] == $fields[$name]['sqlType'] && $fields['length'] != $fields[$name]['length']) {
                    $lines[] = $this->space . '$this->alterColumn("' . $this->_model->tableName() . '", "' . $name . '", "' . $fields[$name]['sqlType'] . '");';
                }
            }

            foreach ($deleted as $name => $field) {
                $lines[] = $this->space . '$this->dropColumn("' . $this->_model->tableName() . '", "' . $name . '");';
            }
        }

        $schema = app()->db->getDb()->getSchema();
        if (empty($lastMigrationFields)) {
            $columns = [];
            foreach ($this->_model->getFieldsInit() as $name => $field) {
                $field->setModel($this->_model);

                if ($field->sqlType() !== false) {
                    if ($field instanceof ForeignField) {
                        $name .= "_id";
                    }

                    $columns[$name] = $field->getSql($schema);
                } else if ($field instanceof ManyToManyField) {
                    /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                    if ($field->through === null) {
                        $lines[] = $this->space . 'if ($this->hasTable("' . $field->getTableName() . '") === false) {';
                        $lines[] = $this->space . $this->space . '$this->createTable("' . $field->getTableName() . '", ' . $this->compileColumns($field->getColumns($schema)) . ', null, true);';
                        $lines[] = $this->space . '}';
                    }
                }
            }

            $lines[] = $this->space . '$this->createTable("' . $this->_model->tableName() . '", ' . $this->compileColumns($columns) . ');';
        } else {
            foreach ($added as $name => $field) {
                $lines[] = $this->space . '$this->addColumn("' . $this->_model->tableName() . '", "' . $name . '", "' . $field['sqlType'] . '");';
            }
        }

        return implode("\n", $lines);
    }

    protected function compileColumns(array $columns)
    {
        $codeColumns = "[\n";
        foreach ($columns as $name => $sql) {
            $codeColumns .= ($this->space . '    ') . '"' . $name . '" => "' . $sql . '",';
            $codeColumns .= "\n";
        }
        $codeColumns .= $this->space . "]";
        return $codeColumns;
    }

    public function getSafeDown()
    {
        if (count($this->getMigrations()) == 0) {
            return $this->space . '$this->dropTable("' . $this->_model->tableName() . '");';
        } else {
            $lines = [];
            $deleted = [];
            $fields = $this->getFields();
            $lastMigrationFields = $this->getLastMigration();

            foreach ($lastMigrationFields as $name => $field) {
                if (array_key_exists($name, $fields) === false) {
                    $added[$name] = $field;
                }
            }

            foreach ($fields as $name => $field) {
                if (array_key_exists($name, $lastMigrationFields) === false) {
                    $deleted[$name] = $field;
                    continue;
                }

                if ($field['hash'] == $lastMigrationFields[$name]['hash']) {
                    continue;
                }

                if ($field['sqlType'] != $lastMigrationFields[$name]['sqlType']) {
                    $lines[] = $this->space . '$this->alterColumn("' . $this->_model->tableName() . '", "' . $name . '", "' . $lastMigrationFields[$name]['sqlType'] . '");';
                } elseif ($field['sqlType'] == $lastMigrationFields[$name]['sqlType'] && $fields['length'] != $lastMigrationFields[$name]['length']) {
                    $lines[] = $this->space . '$this->alterColumn("' . $this->_model->tableName() . '", "' . $name . '", "' . $lastMigrationFields[$name]['sqlType'] . '");';
                }
            }

            foreach ($deleted as $name => $field) {
                $lines[] = $this->space . '$this->dropColumn("' . $this->_model->tableName() . '", "' . $name . '");';
            }
            return implode("\n", $lines);
        }
    }
}
