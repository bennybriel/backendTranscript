<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SaveConfig\Support\StringField;
use App\SaveConfig\Support\CanBeSavedInterface;
use App\SaveConfig\Support\BooleanField;
use App\SaveConfig\Support\IntegerField;
class PaymentRecord extends Model implements CanBeSavedInterface
{
    use HasFactory;
    protected  $table ='u_g_student_accounts';
    //name,matricno,email,phone,guid,programme,state,country
    public function saveableFields():array
    {
        return
        [
            'transactionID'      => StringField::new(),
            'description'        => StringField::new(),
            'amount'             => IntegerField::new(),
            'ispaid'             => BooleanField::new(),
            'matricno'           =>StringField::new(),
            'url'                =>StringField::new(),
            'status'             =>BooleanField::new(),
            'productID'          =>IntegerField::new(),
            'response'           =>StringField::new(),
            'isused'             =>BooleanField::new(),
            'apptype'            =>StringField::new(),
        ];
    }
}
