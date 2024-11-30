<?php

namespace App\SaveConfig\Support;

interface CanBeSavedInterface
{
    public function saveableFields():array;
}