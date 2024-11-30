<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SaveConfig\Support\StringField;
use App\SaveConfig\Support\CanBeSavedInterface;
use App\SaveConfig\Support\BooleanField;
use App\SaveConfig\Support\IntegerField;
class TranscriptApplication extends Model implements CanBeSavedInterface
{
    use HasFactory;
    protected  $table ='applications';
    //name,matricno,email,phone,guid,programme,state,country
    public function saveableFields():array
    {
        return
        [
            'email'      => StringField::new(),
            'state'      => StringField::new(),
            'programme'  =>StringField::new(),
            'name'       =>StringField::new(),
            'matricno'   =>StringField::new(),
            'guid'       =>StringField::new(),
            'phone'      =>StringField::new(),
            'country'    =>IntegerField::new(),
            'transactionID'=>StringField::new(),
            'category'      => StringField::new(),
            'trackID'=>StringField::new(),
        ];
    }
}
