<?php

namespace App\SaveConfig\Support;
use illuminate\Database\Eloquent\Model;

class StringField extends Field
{
    public function execute()
    {
        return $this->value;
    }
}