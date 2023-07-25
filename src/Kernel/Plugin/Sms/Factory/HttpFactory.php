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
namespace Mine\Kernel\Plugin\Sms\Factory;

use Mine\Kernel\Plugin\Sms\Handler\HttpHandler;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class HttpFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $option = $config->get('sms.guards.http', []);

        return \Hyperf\Support\make(HttpHandler::class, [$option]);
    }
}
