<?php
/**
 * Получение списка файлов
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 11:37
 */

namespace FileBrowserClient\Methods;


use FileBrowserClient\Abstracts\Method;

class Resources extends Method
{
    public function get(string $path)
    {
        $path = '/' . ltrim($path, '/');
        $this->client->apiGet('/api/resources' . $path);
        return $this->client->getArray();
    }
}
