<?php

namespace App\SaveConfig\Support;
use illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class DateTimeField extends Field
{
    public function execute()
    {
        if(!$this->value)
        {
            return $this->value;
        }
        return Carbon::parse($this->value)->toDateTimeString();
    }
}