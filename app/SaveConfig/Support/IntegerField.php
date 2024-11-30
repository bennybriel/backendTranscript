<?php

namespace App\SaveConfig\Support;
use illuminate\Database\Eloquent\Model;

class IntegerField extends Field
{
    public function execute()
    {
        return $this->value;
    }
}