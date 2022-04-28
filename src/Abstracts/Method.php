<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 10:38
 */

namespace FileBrowserClient\Abstracts;


use FileBrowserClient\Client;
use FileBrowserClient\Exceptions\ExceptionClient;
use FileBrowserClient\Token;
use Mockery\Exception;

abstract class Method
{
    protected \GuzzleHttp\Client $client;
    protected $uri = '';
    protected $renew = false;

    /* @var \GuzzleHttp\Psr7\Response $response */
    protected $response = null;

    public function __construct()
    {
        if (!defined('FILE_BROWSER_CLIENT_URL')) {
            throw new ExceptionClient('Constant FILE_BROWSER_CLIENT_URL');
        }

        if (!defined('FILE_BROWSER_CLIENT_LOGIN')) {
            throw new ExceptionClient('Constant FILE_BROWSER_CLIENT_LOGIN');
        }


        if (!defined('FILE_BROWSER_CLIENT_PASSWORD')) {
            throw new ExceptionClient('Constant FILE_BROWSER_CLIENT_PASSWORD');
        }
        $this->newClient();
    }

    protected function newClient()
    {
        $token = Token::get();
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => FILE_BROWSER_CLIENT_URL,
            'headers' => [
                'x-auth' => $token
            ]
        ]);

    }

    protected function post(string $uri, $data)
    {
        return $this->send('post', $uri, $data);
    }

    protected function put($uri, $data)
    {
        return $this->send('put', $uri, $data);
    }

    protected function get($uri)
    {
        return $this->send('get', $uri);
    }

    protected function patch(string $uri)
    {
        return $this->send('patch', $uri);
    }

    public function delete(string $uri)
    {
        return $this->send('delete', $uri);
    }


    /**
     * @param $method
     * @param null|string $uri
     * @param null|array $data
     */
    private function send($method, string $uri, $data = null)
    {
        $this->response = null;
        $response = null;
        try {
            $response = $this->client->{$method}($uri, $data);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getCode() === 403 && !$this->renew) {
                $response = $this->errorHandler($method, $uri, $data);
            } else {
                return $e->getMessage();
            }
        }
        $this->response = $response;
        return true;
    }

    public function statusCode()
    {
        return $this->response->getStatusCode();
    }


    public function toArray()
    {
        if (!$this->response) {
            return null;
        }

        if (method_exists($this->response, 'getBody')) {
            $body = $this->response->getBody()->getContents();
            if (empty($body)) {
                return null;
            }
        } else {
            return null;
        }

        return \GuzzleHttp\json_decode($body, true, 512);
    }

    protected function errorHandler($method, $uri = null, $data = null)
    {
        $response = null;
        $this->renew = true;
        // Создаем новый токен
        Token::create();

        // Создаем новый экзепляр клиента
        $this->newClient();

        switch ($method) {
            case 'get':
                $response = $this->{$method}($uri);
                break;
            case 'post':
                $response = $this->{$method}($uri, $data);
                break;
            default:
                break;
        }
        return $response;
    }


    protected function lpath(string $path)
    {
        return '/' . ltrim($path, '/');
    }


    protected function rpath(string $path)
    {
        return rtrim($path, '/') . '/';
    }

}
