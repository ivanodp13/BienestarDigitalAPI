<?php

namespace App\Helpers;

class PasswordGenerator
{
    private $characters;
    private $characterslong;
    private $pass;

    public function newPass()
    {
        $characters = "0123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$#@!?=%-+*.[]{}_,;:<>|";
        $characterslong = strlen($characters);
        $pass = "";

        for($i = 0; $i < 10; $i++) {
            $pass .= $characters[rand(0, $characterslong - 1)];
        }
        return $pass;
    } 
}