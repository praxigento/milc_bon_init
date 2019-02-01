<?php
/**
 * Authors: Alex Gusev <alex@flancer64.com>
 * Since: 2019
 */

namespace Praxigento\Milc\Bonus\Helper;


class Db
    implements \Praxigento\Milc\Bonus\Api\Helper\Db
{
    private const DOCTRINE_DRIVER_MYSQL = 'pdo_mysql';
    private const DOCTRINE_DRIVER_POSTGRES = 'pdo_pgsql';
    private const DOCTRINE_PARAM_DRIVER = 'driver';

    /** @var \TeqFw\Lib\Db\Api\Connection\Main */
    private $conn;

    public function __construct(
        \TeqFw\Lib\Db\Api\Connection\Main $conn
    ) {
        $this->conn = $conn;
    }

    public function isConnectedToMySQL(): bool
    {
        $result = false;
        $params = $this->conn->getParams();
        if (isset($params[self::DOCTRINE_PARAM_DRIVER])) {
            $result = ($params[self::DOCTRINE_PARAM_DRIVER] == self::DOCTRINE_DRIVER_MYSQL);
        }
        return $result;
    }

    public function isConnectedToPostgres(): bool
    {
        $result = false;
        $params = $this->conn->getParams();
        if (isset($params[self::DOCTRINE_PARAM_DRIVER])) {
            $result = ($params[self::DOCTRINE_PARAM_DRIVER] == self::DOCTRINE_DRIVER_POSTGRES);
        }
        return $result;
    }

}