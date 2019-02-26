<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 10/01/2019
 * Time: 22:24
 */

namespace Smokie\LaravelCrudz;


use \Illuminate\Support\Facades\File;

/**
 * Class Field
 * @package App\ResourceGenerator
 */
class Field
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $foreign;
    /**
     * @var
     */
    public $foreignClass;
    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $validationRules = [];

    /**
     * @param $valuePlaceholder
     * @return mixed|string
     */
    public function toHtml($valuePlaceholder)
    {
        $fileGroupHtml = File::get(stub_path('form-group.stub'));

        $type = $this->type;

        $inputHtml = $this->$type($valuePlaceholder);

        $fileGroupHtml = str_replace([
            ':name',
            ':input',
            ':title',
        ], [
            $this->name,
            $inputHtml,
            $this->title,
        ], $fileGroupHtml);

        return $fileGroupHtml;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->type !== $name) {
            return false;
        }

        return str_replace([
            ':name',
            ':title',
            ':value',
        ], [
            $this->name,
            $this->title,
            $arguments[0]
        ], File::get(stub_path('inputs/' . $this->type . '.stub')));
    }

    /**
     * @param $valuePlaceholder
     * @return mixed
     */
    private function foreign($valuePlaceholder)
    {
        return str_replace([
            ':name',
            ':options',
            ':title_column',
            ':default_title',
            ':value_placeholder'
        ], [
            $this->name,
            $this->foreignClass . '::all()',
            'name',
            $this->title,
            $valuePlaceholder

        ], File::get(stub_path('inputs/select.stub')));
    }

    /**
     * @param $valuePlaceholder
     * @return string
     */
    private function enum($valuePlaceholder)
    {
        return collect($this->options)->map(function ($v) use ($valuePlaceholder) {
            static $radioHtml = null;
            $radioHtml = $radioHtml ?: File::get(stub_path('inputs/radio.stub'));

            return str_replace([
                ':name',
                ':title',
                ':value',
                ':v_placeholder',
            ], [
                $this->name,
                title_case($v),
                $v,
                $valuePlaceholder
            ], $radioHtml);

        })->implode("\n");
    }
}