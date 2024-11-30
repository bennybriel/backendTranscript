<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SaveConfig\Support\StringField;
use App\SaveConfig\Support\CanBeSavedInterface;
use App\SaveConfig\Support\BooleanField;
use App\SaveConfig\Support\IntegerField;
class CompleteApplication extends Model implements CanBeSavedInterface
{
    use HasFactory;
    protected  $table ='receipentdata';

    public function saveableFields():array
    {
        return
        [
            'paymentref'          => StringField::new(),
            'matricno'            => StringField::new(),
            'transcriptID'        => StringField::new(),
            'organization'        => StringField::new(),
            'email'               => StringField::new(),
            'phone'               =>StringField::new(),
            'address1'            =>StringField::new(),
            'address2'            =>StringField::new(),
            'contactperson'       =>StringField::new(),
            // 'apptype'            =>StringField::new(),
        ];
    }
}
