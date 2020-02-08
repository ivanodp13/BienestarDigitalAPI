<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usage extends Model
{
    protected $table ='usages';
    protected $filliable = ['date','event','latitude','longitude'];
}
