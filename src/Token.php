<?php
	/**
	 * Получение токена
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 25.04.2022
	 * Time: 10:38
	 */

	namespace FileBrowserClient;


	use Exception;
	use FileBrowserClient\Abstracts\Method;
	use FileBrowserClient\Client;
	use FileBrowserClient\Exceptions\ExceptionClient;

	class Token
	{


		public static function remove()
		{
			if (file_exists(FILE_BROWSER_CLIENT_TOKEN_PATH)) {
				unlink(FILE_BROWSER_CLIENT_TOKEN_PATH);
			}
		}

		/**
		 * @throws ExceptionClient
		 */
		public static function create()
		{

			$Client = new Client();

			$Response = $Client->sendPost('/api/login', [
				'username' => FILE_BROWSER_CLIENT_LOGIN,
				'password' => FILE_BROWSER_CLIENT_PASSWORD,
			]);

			$status = $Client->statusCode();
			if ($status !== 200) {
				throw new ExceptionClient('Code response: ' . $status);
			}

			$token = $Client->getResponse()->getBody()->getContents();
			if (empty($token)) {
				throw new ExceptionClient('Token empty');
			}


			if (!defined('FILE_BROWSER_CLIENT_TOKEN_PATH')) {
				throw new ExceptionClient('Укажите константу для хранения токена FILE_BROWSER_CLIENT_TOKEN_PATH ');
			}

			$path = FILE_BROWSER_CLIENT_TOKEN_PATH;
			self::remove();
			try {
				if (!is_dir(dirname($path))) {
					mkdir(dirname($path), 777, TRUE);
				}
			} catch (Exception $e) {
				throw new ExceptionClient('Не удалось сохранить токен в файл');
			}
			file_put_contents($path, $token);
			if (!file_exists($path)) {
				throw new ExceptionClient('Не удалось сохранить токен в файл');
			}

			return self::get();
		}

		/**
		 * Получение токена
		 * @return false|string|null
		 * @throws ExceptionClient
		 */
		public static function get()
		{
			if (!file_exists(FILE_BROWSER_CLIENT_TOKEN_PATH)) {
				return NULL;
			}

			$token = file_get_contents(FILE_BROWSER_CLIENT_TOKEN_PATH);
			if (strlen($token) !== 508) {
				//throw new ExceptionClient('Токен содержит неправильную длину');
			}
			return $token;
		}
	}
