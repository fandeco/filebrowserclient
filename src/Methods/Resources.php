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
	use FileBrowserClient\Exceptions\ExceptionClient;

	class Resources extends Method
	{
		/**
		 * @throws ExceptionClient
		 */
		public function get(string $path)
		{
			$path = '/' . ltrim($path, '/');
			$this->client->apiGet('/api/resources' . $path);
			return $this->client->getArray();
		}

		/**
		 * @throws ExceptionClient
		 */
		public function move(string $source, string $target)
		{
			$path = '/' . ltrim($source, '/');
			$get  = http_build_query(
				[
					"action"      => "rename",
					"destination" => $target,
					"override"    => 1,
					"rename"      => 0,
				]
			);
			$this->client->apiPatch('/api/resources' . $path . "?$get");
			return $this->client->statusCode();
		}
	}
