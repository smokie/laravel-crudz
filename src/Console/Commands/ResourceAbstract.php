<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 10/01/2019
 * Time: 22:35
 */

namespace Smokie\LaravelCrudz\Console\Commands;

use Smokie\LaravelCrudz\Field;
use Smokie\LaravelCrudz\SQLToField;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;


/**
 * Class AddResourceEdit
 * @package App\Console\Commands
 */
abstract class ResourceAbstract extends Command
{

    /** @var  array */
    protected $fields = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an edit page for a resource controller';

    /**
     * Create Http Request and add validation rules
     * @return bool
     */
    protected function createRequest($name)
    {
        $requestFile = $name . 'Request.php';

        $this->info('Creating request class ' . $requestFile);

        if (file_exists(app_path('Http/Requests/' . $requestFile))) {
            return false;
        }


        Artisan::call('make:request', [
            'name' => str_replace('.php', '', $requestFile)
        ]);

        $requestContent = File::get(app_path('Http/Requests/' . $requestFile));

        $validationArray = collect($this->fields)->map(function ($f) {
            /** @var Field $f */
            $validationLine = collect($f->validationRules)->map(function ($v) {
                return "'{$v}'";
            })->implode(',');
            return "'{$f->name}' => [{$validationLine}]";
        })->implode(",\n\t\t\t");

        File::put(app_path('Http/Requests/' . $requestFile), str_replace('//', $validationArray, $requestContent));

        return true;
    }


    /**
     * @param Model $modelObj
     * @return Field[]
     */
    protected function tableColumnMap(Model $modelObj)
    {

        foreach ($modelObj->getFillable() as $field) {

            $fields [] = (new SQLToField($field, $modelObj))->field();
        }

        return $fields ?? [];
    }

    /**
     * @param $model
     * @return bool
     */
    protected function modelObj($model)
    {
        $className = strpos($model, '\\') === false ? '\\App\\' . $model : $model;
        if (!class_exists($className)) {
            $this->error("Class {$className} does not exist");
            return false;
        }
        $obj = new $className();

        if (!is_subclass_of($obj, Model::class)) {
            $this->error("Class {$className} is not a model class!");
            return false;
        }

        return $obj;
    }

    /**
     * @params string $path
     * @return string
     *
     */
    protected function stubPath($path)
    {
    }

}
