<?php
namespace App\Helpers;
use Firebase\JWT\JWT;
class Token
{
    private $key;
    private $algorithm;
    public function __construct($data = null)
    {
        $this->key = "4rf98h41rfjn4r14jr74he4h98e4h98de-6e4g89e4g978w4g-9w4g-84rg-8w4h48de9h";
        $this->data = $data;
        $this->algorithm = array ('HS256');
    }
    public function encode()
    {
        return JWT::encode($this->data, $this->key);
    }
    public function decode($token)
    {
        return JWT::decode($token, $this->key, $this->algorithm);
    }
}