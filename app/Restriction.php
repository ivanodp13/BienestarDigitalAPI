<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    protected $table ='restrictions';
    protected $filliable = ['MaxTime','InitTime','EndTime'];
}
