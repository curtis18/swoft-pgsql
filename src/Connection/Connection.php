<?php declare(strict_types=1);


namespace Swoft\Pgsql\Connection;

use ReflectionException;
use Swoft;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Connection\Pool\AbstractConnection;
use Swoft\Log\Helper\Log;
use Swoft\Pgsql\PgsqlDb;
use Swoft\Pgsql\Contract\ConnectionInterface;
use Swoft\Pgsql\Exception\PgsqlException;
use Swoft\Pgsql\Pool;
use Swoft\Stdlib\Helper\PhpHelper;
use Throwable;

/**
 * Class Connection
 *
 * @since 2.0
 */
abstract class Connection extends AbstractConnection implements ConnectionInterface
{

    /**
     * @var Pgsql
     */
    protected $client;

    /**
     * @var PgsqlDb
     */
    protected $pgsqlDb;

    /**
     * @param Pool    $pool
     * @param PgsqlDb $pgsqlDb
     */
    public function initialize(Pool $pool, PgsqlDb $pgsqlDb)
    {
        $this->pool     = $pool;
        $this->pgsqlDb  = $pgsqlDb;
        $this->lastTime = time();

        $this->id = $this->pool->getConnectionId();
    }

    /**
     * @throws PgsqlException
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function create(): void
    {
        $this->createClient();
    }

    /**
     * Close connection
     */
    public function close(): void
    {
        $this->client->close();
    }

    /**
     * @throws ReflectionException
     * @throws ContainerException
     * @throws PgsqlException
     */
    public function createClient(): void
    {
        $config = [
            'host'           => $this->pgsqlDb->getHost(),
            'port'           => $this->pgsqlDb->getPort(),
            'user'           => $this->pgsqlDb->getUser(),
            'password'       => $this->pgsqlDb->getPassword(),
            'database'       => $this->pgsqlDb->getDatabase(),
            'schema'         => $this->pgsqlDb->getSchema(),
            'timeout'        => $this->pgsqlDb->getTimeout(),
            'retry_interval' => $this->pgsqlDb->getRetryInterval(),
            'read_timeout'   => $this->pgsqlDb->getReadTimeout(),
        ];

        $this->client = $this->pgsqlDb->getConnector()->connect($config);
    }


    /**
     * @param bool $force
     *
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function release(bool $force = false): void
    {
        /* @var ConnectionManager $conManager */
        $conManager = BeanFactory::getBean(ConnectionManager::class);
        $conManager->releaseConnection($this->id);

        parent::release($force);
    }

    /**
     * @return bool
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function reconnect(): bool
    {
        try {
            $this->create();
        } catch (Throwable $e) {
            Log::error('Pgsql reconnect error(%s)', $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Reconnect to the database if a connection is missing.
     *
     * @return void
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->client)) {
            $this->reconnect();
        }
    }

    /**
     * Run a query statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return array
     * @throws ContainerException
     * @throws DbException
     * @throws ReflectionException
     */
    public function select(string $query, array $bindings = []): array
    {
        $this->reconnectIfMissingConnection();

        if (empty($bindings)) {
            $resource = $this->client->query($query);
        } else {
            $prepare = $this->client->prepare("sqlquery", $query);
            $resource = $this->client->execute("sqlquery", $bindings);
        }
        
        $output = $this->client->fetchAll($resource);

        return ($output == false) ? [] : $output;
    }

    /**
     * Run a select statement and return value array without key
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return array
     * @throws ContainerException
     * @throws DbException
     * @throws ReflectionException
     */
    public function selectFetchNum(string $query, array $bindings = []): array
    {
        $values = [];
        $output = $this->select($query, $bindings);

        foreach($output as $rows) {
            $values[] = array_values($rows);
        }
        return $values;
    }
}
