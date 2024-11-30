<?php

namespace App\SaveConfig\Support;
use illuminate\Database\Eloquent\Model;
use Exception;
class SaveModel
{

    private $model;
    private $data;

    public function __construct(Model $model, array $data)
    {
       $this->model = $model;
       $this->data = $data;
       $modelClassName= $model::class;
       $interfaceClassName = CanBeSavedInterface::class;

       if(!($model instanceof CanBeSavedInterface))
       {
         throw new Exception("This {$modelClassName} must implement {$interfaceClassName} ");
       }

       foreach($data as $column=>$value)
       {
           if(!$this->saveableFieldExists($column))
           {
              
                throw new FieldNotExistException("The field '{$column}' does not exist on '{saveableFields}' method of '{$modelClassName}'");
 
           }

       }
    }
    public function saveableFieldExists(string $column):bool
    {
        return array_key_exists($column, $this->model->saveableFields());
    }
    public function execute(): Model
    {
                foreach($this->data as $column =>$value)
                {
                    $this->model->{$column} = $this->saveableField($column)
                    ->setValue($value)
                    // ->onColumn($column)
                    // ->ofModel($this->model)
                    ->execute();

                }
              $this->model->save();
              return $this->model;
        
    }

    public function  saveableField($column):Field
    {
        return $this->model->saveableFields()[$column];
    }

   
}