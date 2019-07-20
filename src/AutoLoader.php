<?php declare(strict_types=1);


namespace Swoft\Pgsql;


use function bean;
use Pgsql;
use ReflectionException;
use Swoft\Bean\Exception\ContainerException;
use Swoft\SwoftComponent;

/**
 * Class AutoLoader
 *
 * @since 2.0
 */
class AutoLoader extends SwoftComponent
{
    /**
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        return [];
    }

    /**
     * @return array
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function beans(): array
    {
        return [
            'pgsql'      => [
                'class'  => PgsqlDb::class,
            ],
            'pgsql.pool' => [
                'class'   => Pool::class,
                'pgsqlDb' => bean('pgsql')
            ]
        ];
    }
}
