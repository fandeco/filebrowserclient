<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 10:38
 */

namespace FileBrowserClient\Abstracts;


use FileBrowserClient\Client;

abstract class Method
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }
}
