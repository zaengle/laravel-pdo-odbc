<?php

namespace LaravelPdoOdbc;

use PDO;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelPdoOdbc\Contracts\OdbcDriver;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class ODBCConnector extends Connector implements ConnectorInterface, OdbcDriver
{
    /**
     * Establish a database connection.
     *
     * @return PDO
     *
     * @internal param array $options
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        // FULL DSN ONLY
        if ($dsn = Arr::get($config, 'dsn')) {
            $dsn = ! Str::contains('odbc:', $dsn) ? 'odbc:'.$dsn : $dsn;
        }
        // dynamicly build in some way..
        else {
            $dsn = $this->buildDsnDynamicly($config);
        }

        dump($dsn, $config, $options);

      dd(scandir('/opt/snowflake_odbc/lib'));


      $connection = $this->createConnection($dsn, $config, $options);

        return $connection;
    }

    /**
     * Register the connection driver into the DatabaseManager.
     */
    public static function registerDriver(): Closure
    {
        return function ($connection, $database, $prefix, $config) {
            $connection = (new self())->connect($config);
            $connection = new ODBCConnection($connection, $database, $prefix, $config);

            return $connection;
        };
    }

    protected function buildDsnDynamicly(array $config): string
    {
        // ignore some default props...
        $ignoreProps = ['driver', 'odbc_driver', 'dsn', 'options', 'username', 'password'];
        $props = Arr::except($config, $ignoreProps);
        $props = ['driver' => Arr::get($config, 'odbc_driver')] + $props;

        // join pieces together
        $props = array_map(function ($val, $key) {
            return /*';'.*/ucfirst($key).'='.$val;
        }, $props, array_keys($props));

        return 'odbc:'.implode(';', $props);
    }
}
