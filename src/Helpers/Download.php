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

    public function addFile(string $source, string $target)
    {
        if (empty($source)) {
            throw new ExceptionClient('Передан пустой путь');
        }
        $source = '/' . ltrim($source, '/');
        $target = '/' . ltrim($target, '/');
        $this->files[] = [
            'source' => $source,
            'target' => $target
        ];
    }

    public function getFiles()
    {
        return !$this->files ? null : $this->files;
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

        $base_uri = rtrim(FILE_BROWSER_CLIENT_URL, '/') . '/api/raw/';

        $config = [
            'verify' => false,
            'timeout' => 30.0,
            #'debug' => true,
            'base_uri' => $base_uri,
        ];


        $this->client = new \GuzzleHttp\Client($config);

        $downloads = [];
        foreach ($urls as $file) {
            $downloads[] = [
                'source' => ltrim($file['source'], '/') . '?auth=' . $this->token,
                'target' => $file['target']
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

                /* @var \GuzzleHttp\Psr7\Response $Response */
                $Response = $result['value'];

                $code = $Response->getStatusCode();
                if ($result['state'] !== 'fulfilled') {
                    throw new Exception('Не удалось скачать изображение: status ' . $code . $base_uri . $source);
                }


                if ($code !== 200) {
                    throw new Exception('Error download ' . $base_uri . $source);
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
