<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Mine\Kernel\Plugin\Logistics\Factory;

use App\System\Kernel\Plugin\Logistics\Handler\Kuaidi100Handler;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class Kuaidi100Factory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $option = $config->get('logistics.guards.kuaidi100', []);

        return \Hyperf\Support\make(Kuaidi100Handler::class, [$option]);
    }
}
