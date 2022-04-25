<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 24.03.2021
 * Time: 22:49
 */

namespace FileBrowserClient\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;

abstract class TestCase extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }


    /**
     * @param array $data
     * @param bool $success
     * @param string $message
     * @return array
     */
    public function response1c($data = [], $success = true, $message = '')
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
    }
}
