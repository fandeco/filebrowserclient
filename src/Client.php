<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 10:29
 */

namespace FileBrowserClient;


use FileBrowserClient\Exceptions\ExceptionClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

final class Client extends \GuzzleHttp\Client
{


    /* @var string|null $_msg */
    protected $_msg;

    /* @var string|null $_method */
    protected $_method;
    /* @var string|null $_url */
    protected $_url;

    /* @var string $_user_agent */
    protected $_user_agent = 'Mozilla/1.0.0';

    /* @var boolean $_exception_error */
    protected $_exception_error = false;


    /* @var int $_timeout */
    protected $_timeout = 30;
    /* @var int $_connect_timeout */
    protected $_connect_timeout = 3;
    /* @var array $_options */
    private $_options = [];
    /**
     * @var int
     */
    private $_response_status;
    /* @var array $_config */
    private $_config = [];
    /**
     * @var string
     */
    private $_body;

    /**
     * @param array $setting
     * @param boolean $verify // Проверка локального сертификата
     */

    public function __construct(array $config = [])
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

        $config = array_merge([
            'base_uri' => FILE_BROWSER_CLIENT_URL
        ], $config);
        parent::__construct($config);
    }


    public function getMsg()
    {
        return $this->_msg;
    }


    public function userAgent($string)
    {
        $this->_user_agent = $string;
    }

    public function timeout(int $seconds)
    {
        $this->_timeout = $seconds;
    }

    public function connectTimeout(int $seconds)
    {
        $this->_connect_timeout = $seconds;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setOptions($key, $value)
    {
        $this->_config[$key] = $value;
    }

    /**
     * @param $url
     * @param array $config
     * @return bool
     */
    public function sendPost($url, $params = [])
    {
        return $this->sendRequest('post', $url, $params);
    }


    /**
     * @param $url
     * @param array $params
     * @return bool
     */
    public function sendGet($url, $params = [])
    {
        return $this->sendRequest('get', $url, $params);
    }

    public $setToken = false;

    public function token()
    {
        if (!$this->setToken) {
            $this->setToken = true;
            $token = Token::get();
            $this->setOptions('headers', [
                'x-auth' => $token
            ]);
        }
    }

    /**
     * Отправка запроса
     * @param $uri
     * @return bool
     * @throws ExceptionClient
     */
    public function apiGet($uri)
    {
        $this->token();
        $res = $this->sendRequest('get', $uri);
        if ($this->statusCode() !== 200) {
            throw new ExceptionClient('Status code' . $this->statusCode());
        }
        return $res;
    }
	/**
	 * Отправка запроса
	 * @param $uri
	 * @return bool
	 * @throws ExceptionClient
	 */
	public function apiPatch($uri,$params=[])
	{
		$this->token();
		$res = $this->sendRequest('patch', $uri,$params);
		if ($this->statusCode() !== 200) {
			throw new ExceptionClient('Status code' . $this->statusCode());
		}
		return $res;
	}



	/**
     * @param $method
     * @param $url
     * @param array $params
     * @return bool
     */
    private function sendRequest($method, $url, $params = [])
    {
        $this->_exception_error = false;
        $this->_error = false;
        $this->_msg = null;
        $this->_url = null;
        $this->_method = null;
        $this->_options = [];
        $this->response = null;
        $e = null;
        $result = false;
        try {
            $this->_response_status = 200;
            $config = array_merge(
				[
                'http_errors' => true, // Для выброса статуса страницы 400
                'timeout' => $this->_timeout, // Таймаут для содинения
                'connect_timeout' => $this->_connect_timeout, // Время ожидания для конекта
                'headers' => [
                    'User-Agent' => $this->_user_agent
                ],
                #'form_params' => $params,
                'json' => $params
            ], $this->_config);

            $options = array_merge($config, $config);
            $this->_options = $options;
            $this->_url = $url;
            $this->_method = $method;

            if ($method == 'get' && !empty($params)) {
                $url .= '?' . Psr7\build_query($params);
            }
			if(mb_strtolower($method) === 'patch'){
				$this->response = $this->patch($url, $options);
			}else{
            $this->response = $this->request($method, $url, $options);
			}


            # $this->response = $this->{$method}($url, $options);
            $result = true;
        } catch (BadResponseException $e) {
            $this->_error = true;
            $this->_response_status = 500;
        } catch (RequestException $e) {
            $this->_error = true;
            $this->_response_status = 400;
        } catch (\Exception $e) {
            $this->_error = true;
        }


        if (!is_null($e)) {
            if ($e->getCode()) {
                $this->_response_status = $e->getCode();
            }
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();
            }
            $this->_msg = $e->getMessage();
        }

        return $result;
    }


    /**
     * Вернет true если было исключение
     * @return boolean
     */
    public function getBody()
    {
        if ($this->_error) {
            return null;
        }
        if (is_null($this->_body)) {
            if (method_exists($this->response, 'getBody')) {
                $this->_body = $this->response->getBody()->getContents();
            }
        }
        return $this->_body;
    }

    /**
     * Вернет массив ответа или null если не удалось разорабрать ответ
     * @return array|null
     */
    public function getArray()
    {
        $body = $this->getBody();
        if (!$body) {
            return null;
        }
        $array = \GuzzleHttp\json_decode($body, true, 512);
        return is_array($array) ? $array : null;
    }


    /**
     * Вернет true если было исключение
     * @return boolean
     */
    public function isExceptionError()
    {
        return $this->_exception_error;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @return mixed
     */
    public function getDebug()
    {
        return [
            'success' => !$this->_error,
            'response_status' => $this->_response_status,
            'url' => $this->_url,
            'method' => $this->_method,
            'options' => $this->_options,
            'msg' => $this->getMsg(),
            'array' => $this->getArray(),
            'body' => $this->getBody(),
        ];
    }

    public function statusCode()
    {
        return $this->_response_status;
    }

    /**
     * Вернет весь ответ в string
     * @return string|null
     */
    public function getResponseStr()
    {
        return $this->response ? Psr7\str($this->response) : null;
    }


    /**
     * Вернет весь ответ в string
     * @return \GuzzleHttp\Psr7\Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }


}
