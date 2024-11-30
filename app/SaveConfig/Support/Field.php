<?php
namespace App\SaveConfig\Support;

abstract class Field
{
    protected $value;
    protected $model;
    protected $column;

    abstract public function execute();
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    public static function new()
    {
        return new static;
    }
   

    public function onColumn(string $column)
    {
       $this->column = $column;
       return $this;

    }

    public function ofModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function isUpdate(): bool
    {
        return $this->model->exists;
    }
}

