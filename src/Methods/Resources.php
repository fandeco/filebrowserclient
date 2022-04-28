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

    protected function path(string $relativePath)
    {
        $relativePath = $this->lpath($relativePath);
        $relativePath = $this->rpath($relativePath);
        return $relativePath;
    }

    public function has(string $relativePath)
    {
        echo '<pre>';
        print_r($relativePath);
        die;

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
        $source = $this->lpath($source);
        $target = $this->lpath($target);
        $uri = '/api/resources' . $source . '?action=rename&destination=' . $target . '&override=' . $override . '&rename=' . $rename;
        return $this->patch($uri);
    }


    public function find(string $relativePath)
    {
        $list = $this->list($relativePath);
        if ($list === true) {
            return $this->toArray();
        }
        return null;
    }


    /**
     * @param $id
     * @param $data
     * @return bool|string
     */
    public function update($id, $data)
    {
        echo '<pre>';
        print_r($data);
        die;

        return $this->put('/api/resources/' . $id, [
            'json' => $data
        ]);
    }


    /**
     * @param string $relativePath
     * @param bool $force принудительно удалить все файлы и папку
     * @return bool|string|void
     * @throws ExceptionClient
     */
    public function rm(string $relativePath, bool $force = false)
    {
        $relativePath = $this->lpath($relativePath);
        $relativePath = $this->rpath($relativePath);
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
        $relativePath = $this->lpath($relativePath);
        $relativePath = $this->rpath($relativePath);
        $uri = '/api/resources' . $relativePath;
        return $this->post($uri, ['json' => $data]);
    }

}
