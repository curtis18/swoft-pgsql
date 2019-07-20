<?php declare(strict_types=1);


namespace Swoft\Pgsql\Contract;

use Swoft\Pgsql\Connection\Connection;
use Swoft\Pgsql\Pgsql;

/**
 * Class DbSelectorInterface
 *
 * @since 2.0
 */
interface DbSelectorInterface
{
    /**
     * @param Connection $connection
     */
    public function select(Connection $connection): void;
}