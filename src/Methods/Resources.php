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
use FileBrowserClient\Client;
use FileBrowserClient\Exceptions\ExceptionClient;
use FileBrowserClient\Token;

class Resources extends Method
{
    /**
     * @throws ExceptionClient
     */
    public function list(string $relativePath)
    {
        $relativePath = $this->path($relativePath);
        return $this->get('/api/resources' . $relativePath);
    }


    public function has(string $relativePath)
    {
        $relativePath = $this->path($relativePath);
        $item = $this->list($relativePath);
        return $item === true;
    }

    /**
     * @param string $source
     * @param string $target
     * @param bool $override заменить если существует
     * @param false $rename переименовать
     * @return bool|string
     */
    public function move(string $source, string $target, $override = true, $rename = false)
    {
        $source = $this->path($source);
        $target = $this->path($target);
        $uri = '/api/resources' . $source . '?action=rename&destination=' . $target . '&override=' . $override . '&rename=' . $rename;
        return $this->patch($uri);
    }


    public function find(string $name, string $relativePath)
    {
        $list = $this->list($relativePath);
        if ($list === true) {
            $data = $this->toArray();
            foreach ($data['items'] as $item) {
                if ($item['name'] === $name) {
                    return $item;
                }
            }
        }
        return null;
    }


    /**
     * @param string $relativePath
     * @param bool $force принудительно удалить все файлы и папку
     * @return bool|string|void
     * @throws ExceptionClient
     */
    public function rm(string $relativePath, bool $force = false)
    {
        $relativePath = $this->path($relativePath);
        if (!$force) {
            $res = $this->list($relativePath);
            if ($res === true) {
                $item = $this->toArray();
                if ($item['numFiles'] > 0) {
                    $numFiles = $item['numFiles'];
                    return "directory contains files (files {$numFiles}), use forced deletion or clear the contents of the folder manually";
                }
            }
        }
        return $this->delete('/api/resources' . $relativePath);
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function create(string $relativePath, array $data = ['override' => false])
    {
        $relativePath = $this->path($relativePath);
        $uri = '/api/resources' . $relativePath;
        return $this->post($uri, ['json' => $data]);
    }


    protected function path(string $relativePath)
    {
        $relativePath = $this->lpath($relativePath);
        $relativePath = $this->rpath($relativePath);
        return $relativePath;
    }


    public function search(string $query, string $relativePath)
    {
        $relativePath = $this->path($relativePath);
        $relativePath .= '?query=' . $query;
        return $this->get('/api/search' . $relativePath);
    }
}
