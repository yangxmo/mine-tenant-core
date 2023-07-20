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
namespace Mine\Kernel\Plugin\Sms\Construct;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

abstract class AbstructSms
{
    protected string $key;

    protected string $secret;

    protected array $config = [];

    protected Client $clientFactory;

    protected ContainerInterface $container;

    public function __construct(array $config, ContainerInterface $container)
    {
        $this->config = $config;

        $this->key = $config['key'];

        $this->secret = $config['secret'];

        $this->container = $container;

        // 初始化
        $this->initInstance();
    }

    abstract public function initInstance();

    protected function getCode(): int
    {
        return rand(100000, 200000);
    }
}
