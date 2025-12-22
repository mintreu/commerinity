<?php

namespace Mintreu\LaravelIntegration\Support\Fetcher;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Fetch
{

    protected Client $client;


    public static function make(): static
    {
        return new static();
    }

    public static function api(string $method,string $url,array $payload = [],)
    {

    }



    public static function get(string $url,array|\Closure $param)
    {
    }

    public static function post(string $url,\Closure $param,array $payload = [])
    {
    }

    public static function delete(string $url,\Closure $param,array $payload = [])
    {
    }




}
