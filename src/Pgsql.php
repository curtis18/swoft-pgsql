<?php declare(strict_types=1);


namespace Swoft\Pgsql;

use Swoft\Bean\BeanFactory;
use Swoft\Pgsql\Connection\Connection;
use Swoft\Pgsql\Connection\ConnectionManager;
use Swoft\Pgsql\Exception\PgsqlException;
use Throwable;

/**
 * Class Pgsql
 *
 * @since 2.0
 *
 */
class Pgsql
{
    /**
     * @param string $pool
     *
     * @return Connection
     * @throws PgsqlException
     */
    public static function connection(string $pool = Pool::DEFAULT_POOL): Connection
    {
        try {
            /* @var ConnectionManager $conManager */
            $conManager = BeanFactory::getBean(ConnectionManager::class);

            /* @var Pool $pgsqlPool */
            $pgsqlPool  = BeanFactory::getBean($pool);
            $connection = $pgsqlPool->getConnection();

            $connection->setRelease(true);
            $conManager->setConnection($connection);
        } catch (Throwable $e) {
            throw new PgsqlException(
                sprintf('Pool error is %s file=%s line=%d', $e->getMessage(), $e->getFile(), $e->getLine())
            );
        }

        // Not instanceof Connection
        if (!$connection instanceof Connection) {
            throw new PgsqlException(
                sprintf('%s is not instanceof %s', get_class($connection), Connection::class)
            );
        }
        return $connection;
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     * @throws PgsqlException
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $connection = self::connection();
        return $connection->{$method}(...$arguments);
    }
}
