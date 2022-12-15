<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $bool = true;
        $number = 100;
        $status = [
            'enable',
            'disable',
            'pending'
        ];
        $obj = null;

        //测试变量
        //$this->assertTrue(true);
        //$this->assertTrue($bool);
        //$this->assertEquals(100, $number);
        //$this->assertContains('disable', $status);
        //$this->assertCount(2, $status);
        //$this->assertNotEmpty($status);
        //$this->assertNull($obj);

        //测试页面输出
//        echo 'ok';
//        $this->expectOutputString('ok');

        //测试正则
//        echo 'laravel学院';
//        $this->expectOutputRegex('/laravel/i');
    }
}
