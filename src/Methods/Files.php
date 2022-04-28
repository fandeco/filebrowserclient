<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 28.04.2022
 * Time: 10:45
 * Метод для работы с файлами
 * Отличается тем что не добавляет слеш на конец файла
 */

namespace FileBrowserClient\Methods;


use FileBrowserClient\Exceptions\ExceptionClient;

class Files extends Resources
{
    protected function path(string $relativePath)
    {
        $relativePath = $this->lpath($relativePath);
        return $relativePath;
    }


    /**
     * @throws ExceptionClient
     */
    public function info(string $relativePath)
    {
        $relativePath = $this->lpath($relativePath);
        return $this->get('/api/resources' . $relativePath);
    }

    public function has(string $relativePath)
    {
        $relativePath = $this->path($relativePath);
        $item = $this->list($relativePath);
        return $item === true;
    }

}
