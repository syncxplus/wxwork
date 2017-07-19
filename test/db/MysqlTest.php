<?php

namespace test\db;

use data\Mysql;
use PHPUnit\Framework\TestCase;

class MysqlTest extends TestCase
{
    public function testConnection()
    {
        $db = Mysql::db();
        $this->assertNotEmpty($db);
        $this->assertNotEmpty($db->exec('SELECT 1'));
    }
}
