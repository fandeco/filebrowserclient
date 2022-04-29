<?php
/**
 * Получение списка файлов
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 11:37
 */

namespace FileBrowserClient\Methods;


use Exception;
use FileBrowserClient\Abstracts\Method;
use FileBrowserClient\Exceptions\ExceptionClient;

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
		$item         = $this->list($relativePath);
		return $item === TRUE;
	}

	/**
	 * @param string $source
	 * @param string $target
	 * @param bool   $override
	 * @param false  $rename
	 * @return bool|string
	 * @throws Exception
	 */
	public function move(string $source, string $target, $override = TRUE, $rename = FALSE)
	{
		return $this->_patch('rename', $source, $target, $override, $rename);
	}

	/**
	 * @param string $source
	 * @param string $target
	 * @param bool   $override
	 * @param false  $rename
	 * @return bool|string
	 * @throws Exception
	 */
	public function copy(string $source, string $target, $override = TRUE, $rename = FALSE)
	{
		return $this->_patch('copy', $source, $target, $override, $rename);
	}

	/**
	 * @throws Exception
	 */
	private function _patch(string $action, string $source, string $target, $override = TRUE, $rename = FALSE)
	{
		if ($action !== 'rename' and $action !== 'copy') {
			throw new Exception('allowed action rename or copy');
		}

		$source   = $this->path($source);
		$target   = $this->path($target);
		$override = $override ? 'true' : 'false';
		$rename   = $rename ? 'true' : 'false';
		$uri      = '/api/resources' . $source . '?action=' . $action . '&destination=' . $target . '&override=' . $override . '&rename=' . $rename;
		return $this->patch($uri);
	}


	public function find(string $name, string $relativePath)
	{
		$list = $this->list($relativePath);
		if ($list === TRUE) {
			$data = $this->toArray();
			foreach ($data['items'] as $item) {
				if ($item['name'] === $name) {
					return $item;
				}
			}
		}
		return NULL;
	}


	/**
	 * @param string $relativePath
	 * @param bool   $force принудительно удалить все файлы и папку
	 * @return bool|string|void
	 * @throws ExceptionClient
	 */
	public function rm(string $relativePath, bool $force = FALSE)
	{
		$relativePath = $this->path($relativePath);
		if (!$force) {
			$res = $this->list($relativePath);
			if ($res === TRUE) {
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
	public function create(string $relativePath, array $data = ['override' => FALSE])
	{
		$relativePath = $this->path($relativePath);
		$uri          = '/api/resources' . $relativePath;
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
