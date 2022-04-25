<?php
/**
 * Класс занимает скачивание указанных конечных файлов.
 * По умолчанию скачивание присходит во многопоточном режиме
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 11:51
 */

namespace FileBrowserClient\Helpers;

use Exception;
use FileBrowserClient\Exceptions\ExceptionClient;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class Download
{

    protected ?array $files = null;
    protected ?string $target = null;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function targetDir(string $targetDir)
    {
        $this->target = $targetDir;
    }

    public function addFile(string $filePath)
    {
        if (empty($filePath)) {
            throw new ExceptionClient('Передан пустой путь');
        }
        $filePath = ltrim($filePath, '/');
        $this->files[] = $filePath;
    }

    public function getFiles()
    {
        return array_unique($this->files);
    }

    public function resetFiles()
    {
        $this->files = null;
    }

    public function splitArray(array $urls, $limit = 20)
    {
        $urls = array_chunk($urls, $limit);
        return $urls;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function aSyncRequest(array $urls, $exception = true, $limit = 20)
    {
        if (count($urls) > $limit) {
            throw new ExceptionClient('Максимальное количество скачиваемых изображений за 1 раз ' . $limit . ' шт');
        }


        if (!$this->target) {
            throw new ExceptionClient('Не указана директория для сохранения изображений');
        }

        $config = [
            'verify' => false,
            'timeout' => 30.0,
            #'debug' => true,
            'base_uri' => rtrim(FILE_BROWSER_CLIENT_URL, '/') . '/api/raw/',
        ];


        $this->client = new \GuzzleHttp\Client($config);

        $downloads = [];
        foreach ($urls as $file) {
            $downloads[] = [
                'source' => $file . '?auth=' . $this->token,
                'target' => $this->target . basename($file)
            ];
        }

        $promises = [];
        foreach ($downloads as $k => $data) {
            $source = $data['source'];
            $target = $data['target'];

            if (file_exists($target)) {
                // Удаляем для безопасности
                unlink($target);
            }

            $promises[] = $this->client->getAsync($source, ['sink' => $target]);
        }


        // Дождемся завершения запросов, даже если некоторые из них завершатся неудачно
        $results = Promise\settle($promises)->wait();
        foreach ($results as $k => $result) {
            $data = $downloads[$k];
            $target = $data['target'];
            $source = $data['source'];

            // Записываем состояние
            $state = $result['state'];
            $downloads[$k]['state'] = $state;

            if ($exception) {
                if ($result['state'] !== 'fulfilled') {
                    throw new Exception('Не удалось скачать изображение' . $source);
                }


                $code = $result['value']->getStatusCode();
                if ($code !== 200) {
                    throw new Exception('Error download ' . $source);
                }

                if (!file_exists($target)) {
                    throw new Exception('Изображение не загружено ' . $target);
                }
            }
            $this->results[$target] = $state;

        }

        return $downloads;
    }
}
