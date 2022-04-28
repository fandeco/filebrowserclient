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
        $list = $Resource->list('/');
        self::assertArrayHasKey('items', $Resource->toArray());
    }

    public function testGetDir()
    {
        $Resource = new Resources();
        $list = $Resource->list('/ФОТОБАНК/B.Lux/');
        self::assertTrue($list);
        self::assertArrayHasKey('items', $Resource->toArray());
    }

    public function testFind()
    {
        $Resource = new Resources();
        $item = $Resource->find("vendorDir", '/NewCatalog');
        self::assertArrayHasKey('name', $item);
    }

    public function testList()
    {
        $Resource = new Resources();
        $list = $Resource->list('/');
        self::assertTrue($list);
        self::assertArrayHasKey('items', $Resource->toArray());
    }

    public function testCreate()
    {
        $Resource = new Resources();

        $relativePath = '/NewCatalog/vendorDir';
        $item = $Resource->list($relativePath);
        self::assertTrue($item !== true);

        $create = $Resource->create($relativePath);
        self::assertTrue($create);
        self::assertEquals(200, $Resource->statusCode());
    }

    public function testRm()
    {
        $Resource = new Resources();
        $relativePath = '/NewCatalog/vendorDir';
        $rm = $Resource->rm($relativePath);
        self::assertTrue($rm);
        self::assertEquals(200, $Resource->statusCode());
    }

    public function testHas()
    {
        $Resource = new Resources();
        self::assertTrue($Resource->has('/NewCatalog/vendorDir'));
    }

    public function testQuery()
    {
        $Resource = new Resources();
        $res = $Resource->search('vendorDir', '/NewCatalog/');
        self::assertTrue($res);
    }
}
