<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 10/01/2019
 * Time: 22:35
 */

namespace Smokie\LaravelCrudz;

use Illuminate\Support\Facades\DB;

class SQLToField
{
    /**
     * @var Field
     */
    private $_field;


    /**
     * SQLToField constructor.
     * @param string $fieldName
     * @param \Illuminate\Database\Eloquent\Model $modelObj
     */
    public function __construct($fieldName, $modelObj)
    {
        static $showCreateResult = null;
        static $showCreateSQL = null;
        $showCreateResult = $showCreateResult ?: DB::connection()->select("SHOW CREATE TABLE {$modelObj->getTable()}");
        $showCreateSQL = $showCreateSQL ?: array_values(get_object_vars($showCreateResult[0]))[1];

        preg_match_all("/`({$fieldName})` (\w+)/", $showCreateSQL, $matches);

        $fieldObj = new Field();

        list($fieldObj->name, $type) = collect($matches)->pluck(0)->only(1, 2)->values();

        $fieldObj->type = $this->inputType($type);
        $fieldObj->title = title_case($fieldObj->name);

        if ($fieldObj->type === 'text' && $fieldObj->name === 'color') {
            $fieldObj->type = 'color';
        }

        if ($fieldObj->type === 'number' && substr($fieldObj->name, -3, 3) === '_id') {
            $foreignFn = str_replace('_id', '', $fieldObj->name);
            if (method_exists($modelObj, $foreignFn)) {
                $fieldObj->type = 'foreign';

                $relation = $modelObj->{$foreignFn}();
                $fieldObj->foreignClass = get_class($relation->getRelated());
                $fieldObj->foreign = $foreignFn;
            }
        }

        if ($fieldObj->type === 'enum') {
            preg_match_all("/`({$fieldName})`\s+enum\(([^\)]+)\)/", $showCreateSQL, $options);
            $options = collect(explode(',', $options[2][0]))->map(function ($v) {
                return str_replace(['"', '\''], '', $v);
            });
            $fieldObj->options = $options->toArray();

            //@ add 'in' rule
            $fieldObj->validationRules [] = 'in:' . $options->implode(',');
        }


        // check whether field is required
        if (preg_match("/`{$fieldName}`.+?(?=NOT NULL)/", $showCreateSQL)) {
            $fieldObj->validationRules [] = 'required';
        }

        $this->_field = $fieldObj;


    }

    public function field()
    {
        return $this->_field;
    }

    /**
     * @param $sqlType
     * @return string
     */
    private function inputType($sqlType)
    {
        static $map = [
            'varchar' => 'text',
            'char' => 'text',
            'tinytext' => 'text',
            'mediumtext' => 'text',
            'longtext' => 'text',
            'tinyblob' => 'text',
            'longblob' => 'text',
            'int' => 'number',
            'float' => 'float',
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'bigint' => 'number',
            'double' => 'float',
            'real' => 'number',
            'year' => 'number',
            'decimal' => 'number',
            'text' => 'textarea',
            'bool' => 'boolean',
            'enum' => 'enum',
            'date' => 'date',
            'datetime' => 'datetime',
            'time' => 'time',
        ];
        return $map[$sqlType] ?? 'text';
    }
}