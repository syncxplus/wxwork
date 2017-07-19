<?php

namespace data;

use DB\SQL;

class Mysql extends SQL\Mapper
{
    private static $mysql;

    static function db()
    {
        if (self::$mysql == null) {
            $f3 = \Base::instance();
            self::$mysql = new SQL(
                $f3->get('MYSQL_DSN'),
                $f3->get('MYSQL_USER'),
                $f3->get('MYSQL_PASSWORD')
            );
        }
        return self::$mysql;
    }

    function merge($data)
    {
        if (is_array($data)) {
            $fields = $this->fields();
            foreach ($data as $fieldName => $fieldValue) {
                if (in_array($fieldName, $fields)) {
                    $this->set($fieldName, $fieldValue);
                }
            }
        }
    }

    function __construct($table, $ttl = 0)
    {
        parent::__construct(self::db(), $table, null, $ttl);
    }
}
