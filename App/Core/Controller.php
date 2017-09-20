<?php

namespace App\Core;

class Controller
{
    
    public function model($model)
    {
        $class = "\App\Models\\$model";
        return new $class();
    }
    
    public function view($view, $data = [])
    {
        if(file_exists('../App/View/'.$view.'.php'))
        {
            require_once '../App/View/'.$view.'.php';
        }
        else
        {
            error_404();
        }
    }
    
}