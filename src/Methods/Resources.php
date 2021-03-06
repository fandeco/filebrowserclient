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
     * @param bool $override
     * @param false $rename
     * @return bool|string
     * @throws \Exception
     */
    public function move(string $source, string $target, $override = true, $rename = false)
    {
        return $this->_patch('rename', $source, $target, $override, $rename);
    }

    /**
     * @param string $source
     * @param string $target
     * @param bool $override
     * @param false $rename
     * @return bool|string
     * @throws \Exception
     */
    public function copy(string $source, string $target, $override = true, $rename = false)
    {
        return $this->_patch('copy', $source, $target, $override, $rename);
    }

    private function _patch(string $action, string $source, string $target, $override = true, $rename = false)
    {
        if ($action !== 'rename' && $action !== 'copy') {
            throw new \Exception('allowed action "rename" or "copy"');
        }

        $source = $this->path($source);
        $target = $this->path($target);
        $override = $override ? 'true' : 'false';
        $rename = $rename ? 'true' : 'false';

        $uri = $this->encodeURI('/api/resources' . $source);
		$uri = strtr($uri,
					 [
						 '%252B'=>'%2B',
						 '%2520'=>' ',
					 ]
		);
        $action = '?action=' . $action . '&destination=' . $target . '&override=' . $override . '&rename=' . $rename;
        $action = $this->encodeURI($action);
        $uri .= str_ireplace('%2B', '%252B', $action);
        return $this->patch($uri);
    }


    function encodeURI($url)
    {
        if (empty($url)) return $url;

        $res = preg_match('/.*:\/\/(.*?)\//', $url, $matches);
        if ($res) {
            // except host name
            $url_tmp = str_replace($matches[0], "", $url);

            // except query parameter
            $url_tmp_arr = explode("?", $url_tmp);

            // encode each tier
            $url_tear = explode("/", $url_tmp_arr[0]);
            foreach ($url_tear as $key => $tear) {
                $url_tear[$key] = rawurlencode($tear);
            }

            $ret_url = $matches[0] . implode('/', $url_tear);

            // encode query parameter
            if (count($url_tmp_arr) >= 2) {
                $ret_url .= "?" . $this->encodeURISub($url_tmp_arr[1]);
            }
            return $ret_url;
        } else {
            return $this->encodeURISub($url);
        }

    }

    /**
     * https://stackoverflow.com/questions/4929584/encodeuri-in-php/6059053
     */
    function encodeURISub($url)
    {
        // http://php.net/manual/en/function.rawurlencode.php
        // https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/encodeURI
        $unescaped = array(
            '%2D' => '-', '%5F' => '_', '%2E' => '.', '%21' => '!', '%7E' => '~',
            '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'
        );
        $reserved = array(
            '%3B' => ';', '%2C' => ',', '%2F' => '/', '%3F' => '?', '%3A' => ':',
            '%40' => '@', '%26' => '&', '%3D' => '=', '%24' => '$'
        );
        $score = array(
            '%23' => '#'
        );
        return strtr(rawurlencode($url), array_merge($reserved, $unescaped, $score));

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

    /**
     * @throws ExceptionClient
     */
    public function upload(string $fileName, $content, $override = FALSE)
    {
        $override = $override ? 'true' : 'false';
        return $this->curlPost('/api/resources/' . $fileName . '?override=' . $override, $content);
    }
}
