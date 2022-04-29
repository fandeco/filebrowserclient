<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 28.04.2022
 * Time: 10:50
 */

namespace FileBrowserClient\Tests\Methods;

use FileBrowserClient\Methods\Files;
use FileBrowserClient\Tests\TestCase;

class FilesTest extends TestCase
{
    public function testInfo()
    {
        $Resource = new Files();
        $list = $Resource->info('/NewCatalog/A4344SP-1BK_2.jpg');
        self::assertTrue($list);
    }

    public function testMove()
    {
        $Resource = new Files();
        $Resource->move('/NewCatalog/vendorDir/A4344SP-1BK_2.jpg', '/NewCatalog/A4344SP-1BK_2.jpg');
        self::assertEquals(200, $Resource->statusCode());
        $Resource->move('/NewCatalog/A4344SP-1BK_2.jpg', '/NewCatalog/vendorDir/A4344SP-1BK_2.jpg');
        self::assertEquals(200, $Resource->statusCode());
    }

    public function testQuery()
    {
        $Resource = new Files();
        $list = $Resource->search('4344SP-1BK_2.jpg', '/NewCatalog');
        self::assertTrue($list);

        echo '<pre>';
        print_r($Resource->toArray());
        die;

    }
}
