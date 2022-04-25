<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 25.04.2022
 * Time: 11:29
 */

namespace FileBrowserClient\Tests;

use FileBrowserClient\Tests\TestCase;
use FileBrowserClient\Token;

class TokenTest extends TestCase
{

    public function testCreateToken()
    {
        $token = Token::create();
        self::assertTrue(!empty($token));
    }
}
