<?php

	namespace index;
	define('FILE_BROWSER_CLIENT_URL', "https://filebrowser.massive.ru");
	define('FILE_BROWSER_CLIENT_LOGIN', "loader");
	define('FILE_BROWSER_CLIENT_PASSWORD', "YR!>-r$5BY)i'7v23");
	define('FILE_BROWSER_CLIENT_TOKEN_PATH', dirname(__FILE__, 2) . '/token');

	require "../vendor/autoload.php";

	use FileBrowserClient\Methods\Resources;
	use FileBrowserClient\Token;

	Token::create();

	function testMove()
	{
		$Resource = new Resources();
		$list     = $Resource->move("/EXCHANGER/move Test 1/A1003AP-1CC.jpg", '/EXCHANGER/move Test 2/A1003AP-1CC.jpg');
		echo '<pre>';
		var_dump($list);
		die;
	}

	testMove();