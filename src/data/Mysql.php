<?php

namespace data;

use DB\SQL;

class Mysql extends SQL\Mapper
{
    static $NAME = 'MYSQL';

    static function db()
    {
        if (!\Registry::exists(self::$NAME)) {
            $f3 = \Base::instance();
            \Registry::set(self::$NAME, new SQL(
                $f3->get('MYSQL_DSN'),
                $f3->get('MYSQL_USER'),
                $f3->get('MYSQL_PASSWORD')
            ));
        }
        return \Registry::get(self::$NAME);
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
