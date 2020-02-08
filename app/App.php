<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class App extends Model
{
    protected $table ='apps';
    protected $filliable = ['name','icon'];
}
