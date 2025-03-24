<?php

namespace Maksimriabchenko\Servertime\Tests;

use Maksimriabchenko\Servertime\ServerDateTime;
use PHPUnit\Framework\TestCase;

class ServerDateTimeMyIPTest extends TestCase
{
    /**
     * 31.43.136.76
     */
    public function testDateTime()
    {
        $data = (new ServerDateTime())->getDateTime("31.43.136.76");
        $this->assertSame($data[0], ServerDateTime::GOOD);
        echo "current datetime for 31.43.136.76 is " . ($data[1]->format((new ServerDateTime())->date_time_format)) . "\n";
    }
}