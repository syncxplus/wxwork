<?php

namespace data;

use DB\SQL\Mapper;

class SqlMapper extends Mapper
{
    function __construct($table, $ttl = 0)
    {
        parent::__construct(Mysql::instance()->get(), $table, null, $ttl);
    }
}
