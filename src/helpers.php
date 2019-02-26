<?php
/**
 * Created by PhpStorm.
 * User: taherodeh
 * Date: 26/02/2019
 * Time: 17:04
 */

function stub_path($path)
{
    $p = resource_path('crudz/stubs/' . $path);
    return \Illuminate\Support\Facades\File::exists($p)
        ? $p
        : __DIR__ . '/../stubs/' . $path;
}