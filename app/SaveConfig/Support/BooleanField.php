<?php

namespace App\SaveConfig\Support;
use illuminate\Database\Eloquent\Model;

class BooleanField extends Field
{
    public function execute()
    {
        return in_array($this->value, [1, '1', true, 'true', 'on','yes']);
    }
}