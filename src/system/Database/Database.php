<?php declare(strict_types=1);
namespace system\Database;

use \configuration\Database as DBCONFIG;

class Database {
    /**
     * Connects to a database
     * 
     * If no argument is present the default database is used.
     * 
     * @param array|null $info Default: null
     */
    public static function connect(array $info = null)
    {
        $connection = null;

        switch ($info['driver'] ?? DBCONFIG::DRIVER) {
            case 'mysqli':
            default:
                $connection = self::mysqli($info);
                break;
        }
        return $connection;
    }

    /**
     * Establishes a connection to a mysqli database
     * 
     * @param array|null $info Default: null
     * 
     * @return \mysqli
     */
    private static function mysqli(array $info = null): \mysqli
    {
        $mysqli = new \mysqli(
            $info['host'] ?? DBCONFIG::HOST,
            $info['user'] ?? DBCONFIG::USER,
            $info['password'] ?? DBCONFIG::PASSWORD,
            $info['databasename'] ?? DBCONFIG::DATABASENAME
        );

        if (DBCONFIG::USE_UTF8) {
            $mysqli->set_charset('utf8');
        }
        $mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        if ($mysqli->connect_error) {
            die ("Connection error: " . $mysqli->connect_error);
        }

        return $mysqli;
    }
}
