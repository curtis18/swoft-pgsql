<?php declare(strict_types=1);


namespace Swoft\Pgsql\Connector;

use Swoole\Coroutine\PostgreSQL;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Pgsql\Contract\ConnectorInterface;
use Swoft\Pgsql\Exception\PgsqlException;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Stdlib\Helper\JsonHelper;
use function sprintf;
use Throwable;

/**
 * Class PgsqlConnector
 *
 * @since 2.0
 *
 * @Bean()
 */
class PgsqlConnector implements ConnectorInterface
{
    /**
     * @param array $config
     *
     * @return PostgreSQL
     * @throws Throwable
     */
    public function connect(array $config): PostgreSQL
    {
        $client = new PostgreSQL();
        
        try {
            $connection = $client->connect("host=".$config['host']." port=".$config['port']." dbname=".$config['database']." user=".$config['user']." password=".$config['password']);
        } catch (Throwable $e) {
            throw new PgsqlException(
                sprintf('Pgsql cannot be connected!')
            );
        }

        if ($connection === false){
            throw new PgsqlException(
                sprintf('Pgsql connection fail!')
            );
        } else {
            if (!empty($config['schema'])){
                $client->query("SET search_path TO ".implode(",", $config['schema']).";");
            } else {
                $client->query("SET search_path TO public;");
            }
        }

        return $client;
    }

    /**
     * @param array $config
     *
     * @return object
     * @throws Throwable
     */
    public function pgConnect(array $config)
    {
        try {
            $connection = pg_connect("host=".$config['host']." port=".$config['port']." dbname=".$config['database']." user=".$config['user']." password=".$config['password']);
        } catch (Throwable $e) {
            throw new PgsqlException(
                sprintf('Pgsql cannot be connected!')
            );
        }

        if ($connection === false){
            throw new PgsqlException(
                sprintf('Pgsql connection fail!')
            );
        } else {
            if (!empty($config['schema'])){
                pg_query($connection, "SET search_path TO ".implode(",", $config['schema']).";");
            } else {
                pg_query($connection, "SET search_path TO public;");
            }
        }

        return $connection;
    }
}
