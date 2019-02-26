<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 26/02/2019
 * Time: 17:04
 */

function stub_path($path)
{
    $p = 'crudz/stubs/' . resource_path($path);
    return \Illuminate\Support\Facades\File::exists($p)
        ? resource_path($p)
        : __DIR__ . '/../stubs/' . $path;
}