<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SaveConfig\Support\StringField;
use App\SaveConfig\Support\CanBeSavedInterface;
use App\SaveConfig\Support\BooleanField;
use App\SaveConfig\Support\IntegerField;
class Registration extends Model implements CanBeSavedInterface
{
    use HasFactory;
    protected  $table ='users';
    public function saveableFields():array
    {
        return
        [
            'email'     => StringField::new(),
            'password'  => StringField::new(),
            'usertype'  =>StringField::new(),
            'name'      =>StringField::new(),
            'matricno'  =>StringField::new(),
            'guid'      =>StringField::new(),
            'phone'      =>StringField::new(),
        ];
    }
}
