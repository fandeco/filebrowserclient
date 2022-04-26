<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 11:53
 */

namespace FileBrowserClient\Tests\Helpers;

use FileBrowserClient\Helpers\Download;
use FileBrowserClient\Methods\Resources;
use FileBrowserClient\Tests\TestCase;
use FileBrowserClient\Token;

class DownloadTest extends TestCase
{
    public function testGet()
    {
        $token = Token::get();
        $targetPath = SAVE_PATH;
        $Resource = new Resources();
        $list = $Resource->get(DOWNLOAD_PATH);
        $items = $list['items'];

        // Скачиваем
        $Load = new Download($token);
        foreach ($items as $item) {
            if (!$item['isDir']) {
                $target = $targetPath . $item['name'];
                $Load->addFile($item['path'], $target);
            }
        }


        // Скачивание целой директории
        if ($files = $Load->getFiles()) {
            $limit = 50;

            $files = $Load->splitArray($files);

            foreach ($files as $array) {
                $results = $Load->aSyncRequest($array, true, $limit);
            }

        }
        echo '<pre>';
        print_r(22);
        die;

        // Скачиваем


    }
}
