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
			$Client = new Client();
			$options = [
				'headers' => [
					'x-auth' => $token = Token::get()
				]
			];
			$res = $Client->patch('/api/resources' . $source . '?action=rename&destination=' . $target . '&override=true&rename=false', $options);
			return $res->getStatusCode();
		}
	}
