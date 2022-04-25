<?php
	/**
	 * Created by Andrey Stepanenko.
	 * User: webnitros
	 * Date: 25.04.2022
	 * Time: 11:39
	 */

	namespace FileBrowserClient\Tests\Methods;

	use FileBrowserClient\Methods\Resources;
	use FileBrowserClient\Tests\TestCase;

	class ResourcesTest extends TestCase
	{

		public function testGet()
		{
			$Resource = new Resources();
			$list     = $Resource->get('/');
			self::assertArrayHasKey('items', $list);
		}

		public function testGetDir()
		{
			$Resource = new Resources();
			$list     = $Resource->get('/ФОТОБАНК/B.Lux/');
			self::assertArrayHasKey('items', $list);
		}

		public function testMove()
		{
			$Resource = new Resources();
			$list     = $Resource->move(urlencode("/EXCHANGER/move Test 1/A1003AP-1CC.jpg"), urlencode('/EXCHANGER/move Test 2/A1003AP-1CC.jpg'));
			echo '<pre>';
			var_dump($list);
			die;

			self::assertArrayHasKey('items', $list);
		}
	}
