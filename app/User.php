<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
class User extends Model
{
    protected $table ='users';
    protected $filliable = ['name','email','password'];
    
    
}