<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 08/01/15 14:34
 */

namespace Mindy\Orm;

use Exception;
use Mindy\Helper\Json;
use Mindy\Helper\Traits\Accessors;

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
     * @var string used for unit testing only
     */
    private $_name;

    public function __construct(Model $model, $path)
    {
        $this->_model = $model;
        if (!is_dir($path)) {
            throw new Exception("Directory $path not found");
        }
        $this->_path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public function generateName()
    {
        if ($this->_name !== null) {
            $name = $this->_name;
        } else {
            $name = $this->_model->classNameShort();
        }
        return $name . '_' . time() . '.json';
    }

    public function getFields()
    {
        $fields = [];
        $modelFields = $this->_model->getFieldsInit();
        foreach ($modelFields as $name => $field) {
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
        return Json::encode($this->getFields());
    }

    public function getMigrations()
    {
        if ($this->_name !== null) {
            $name = $this->_name;
        } else {
            $name = $this->_model->classNameShort();
        }
        return glob($this->_path . '/' . $name . '_*.json');
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
        $this->_name = $name;
    }
}
