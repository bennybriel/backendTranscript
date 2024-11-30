<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\SaveConfig\Support\StringField;
use App\SaveConfig\Support\CanBeSavedInterface;
class RequestLogger extends Model implements CanBeSavedInterface
{
    use HasFactory;
    protected  $table ='requestlogger';
    public function saveableFields():array
    {
        return
        [
            'request'         => StringField::new(),
        ];

    }
}
