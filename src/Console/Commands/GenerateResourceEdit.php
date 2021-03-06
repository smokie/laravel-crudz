<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 10/01/2019
 * Time: 22:35
 */
namespace Smokie\LaravelCrudz\Console\Commands;

use Smokie\LaravelCrudz\Field;
use Illuminate\Support\Facades\File;


/**
 * Class AddResourceEdit
 * @package App\Console\Commands
 */
class GenerateResourceEdit extends ResourceAbstract
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudz:edit {model : Model Name}';


    public function handle()
    {
        $model = $this->argument('model');

        $obj = $this->modelObj($model);

        if (!$obj) {
            return false;
        }


        $viewDir = resource_path('views/' . $obj->getTable());
        $viewPath = $viewDir . '/edit.blade.php';
        if (!File::isDirectory($viewDir)) {
            if (!File::makeDirectory($viewDir)) {
                $this->error('Cannot make view directory ' . $viewDir);
                return false;
            }
        }

        if (File::exists($viewPath)) {
            if (!$this->confirm("View $viewPath exists, overwrite ?")) {
                return false;
            }
        }

        $this->fields = $this->tableColumnMap($obj);

        $viewHtml = File::get(stub_path('edit-page.stub'));
        $modelName = strtolower(preg_replace('/([^\\\]+\\\)+/', '', get_class($obj)));

        $viewHtml = str_replace([
            ':action',
            ':method',
            ':back',
            ':buttonLabel',
            ':fields_output',
        ], [
            'route("' . $obj->getTable() . '.update", [$' . $modelName . '])',
            'PUT',
            'route("' . $obj->getTable() . '.index")',
            'Update',
            implode("\n", collect($this->fields)->map(function (Field $f) use ($modelName) {
                return $f->toHtml('$' . $modelName . '->' . $f->name);

            })->toArray())
        ], $viewHtml);


        if (File::put($viewPath, $viewHtml) <= 0) {
            $this->error('View exists, are you sure you want ');
            return false;
        }

        $this->info("created edit view $viewPath");

        $this->createRequest($model);
    }

}
