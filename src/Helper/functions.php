<?php
/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://gitee.com/xmo/MineAdmin
 */

use App\System\Service\SystemQueueLogService;
use Mine\Vo\AmqpQueueVo;
use Mine\Vo\QueueMessageVo;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Context\ApplicationContext;
use Mine\Helper\LoginUser;
use Mine\Helper\AppVerify;
use Mine\Helper\Id;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Mine\Kernel\Tenant\Tenant;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;

if (! function_exists('dd')) {
    /**
     * 获取环境变量信息.
     *
     * @param mixed ...$data
     * @return mixed
     */
    function dd($data)
    {
        print_r($data);
        exit;
    }
}

if (! function_exists('env')) {
    /**
     * 获取环境变量信息.
     */
    function env(string $key, mixed $default = null): mixed
    {
        return \Hyperf\Support\env($key, $default);
    }
}

if (! function_exists('config')) {
    /**
     * 获取配置信息.
     */
    function config(string $key, mixed $default = null): mixed
    {
        return \Hyperf\Config\config($key, $default);
    }
}

if (! function_exists('make')) {
    /**
     * Create an object instance, if the DI container exist in ApplicationContext,
     * then the object will be created by DI container via `make()` method, if not,
     * the object will create by `new` keyword.
     */
    function make(string $name, array $parameters = [])
    {
        return \Hyperf\Support\make($name, $parameters);
    }
}

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @return mixed|ContainerInterface
     */
    function di(?string $id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}

if (! function_exists('queue_push')) {
    /**
     * Push a job to async queue.
     */
    function queue_push(JobInterface $job, int $delay = 0, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        return $driver->push($job, $delay);
    }
}

if (! function_exists('go')) {
    function go(callable $callable): void
    {
        $id = Tenant::instance()->getId();

        \Swoole\Coroutine::create(function () use ($id, $callable) {
            Tenant::instance()->init($id);
            \Hyperf\Support\call($callable);
        });
    }
}

if (! function_exists('shortMd5')) {
    /**
     * 返回16位md5值
     */
    function shortMd5(): string
    {
        return substr(md5(uniqid(strval(mt_rand()), true)), 8, 16);
    }
}

if (! function_exists('getContainerRedisHandle')) {
    /**
     * 返回16位md5值
     * @return \Hyperf\Redis\RedisProxy
     */
    function getContainerRedisHandle(string $pool = 'default'): Hyperf\Redis\RedisProxy
    {
        return ApplicationContext::getContainer()->get(\Hyperf\Redis\RedisFactory::class)->get($pool);
    }
}


if (! function_exists('container')) {

    /**
     * 获取容器实例
     * @return ContainerInterface
     */
    function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

}

if (! function_exists('redis')) {

    /**
     * 获取Redis实例
     * @param string $poolName
     * @return RedisProxy
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function redis(string $poolName = 'default'): RedisProxy
    {
        return container()->get(\Hyperf\Redis\RedisFactory::class)->get($poolName);
    }

}

if (! function_exists('console')) {

    /**
     * 获取控制台输出实例
     * @return StdoutLoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function console(): StdoutLoggerInterface
    {
        return container()->get(StdoutLoggerInterface::class);
    }

}

if (! function_exists('logger')) {

    /**
     * 获取日志实例
     * @param string $name
     * @return LoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function logger(string $name = 'Log'): LoggerInterface
    {
        return container()->get(LoggerFactory::class)->get($name);
    }

}

if (! function_exists('user')) {
    /**
     * 获取当前登录用户实例
     * @param string $scene
     * @return LoginUser
     */
    function user(string $scene = 'default'): LoginUser
    {
        return new LoginUser($scene);
    }
}

if (! function_exists('format_size')) {
    /**
     * 格式化大小
     * @param int $size
     * @return string
     */
    function format_size(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $index = 0;
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
            $index = $i;
        }
        return round($size, 2) . $units[$index];
    }
}

if (! function_exists('t')) {
    /**
     * 多语言函数
     * @param string $key
     * @param array $replace
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function t(string $key, array $replace = []): string
    {
        $acceptLanguage = container()->get(\Mine\MineRequest::class)->getHeaderLine('accept-language');
        $language = !empty($acceptLanguage) ? explode(',',$acceptLanguage)[0] : 'zh_CN';
        return __($key, $replace, $language);
    }
}

if (! function_exists('mine_collect')) {
    /**
     * 创建一个Mine的集合类
     * @param null|mixed $value
     * @return \Mine\MineCollection
     */
    function mine_collect($value = null): \Mine\MineCollection
    {
        return new \Mine\MineCollection($value);
    }
}

if (! function_exists('context_set')) {
    /**
     * 设置上下文数据
     * @param string $key
     * @param $data
     * @return bool
     */
    function context_set(string $key, $data): bool
    {
        return (bool)\Hyperf\Context\Context::set($key, $data);
    }
}

if (! function_exists('context_get')) {
    /**
     * 获取上下文数据
     * @param string $key
     * @return mixed
     */
    function context_get(string $key)
    {
        return \Hyperf\Context\Context::get($key);
    }
}

if (! function_exists('app_verify')) {
    /**
     * 获取APP应用请求实例
     * @param string $scene
     * @return AppVerify
     */
    function app_verify(string $scene = 'api'): AppVerify
    {
        return new AppVerify($scene);
    }
}

if (! function_exists('snowflake_id')) {
    /**
     * 生成雪花ID
     * @param int|null $workerId
     * @return String
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function snowflake_id(?int $workerId = null): String
    {
        return container()->get(Id::class)->getId($workerId);
    }
}

if (! function_exists('event')) {
    /**
     * 事件调度快捷方法
     * @param object $dispatch
     * @return object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function event(object $dispatch): object
    {
        return container()->get(EventDispatcherInterface::class)->dispatch($dispatch);
    }
}

if (! function_exists('push_queue_message')) {
    /**
     * 推送消息到队列
     * @param QueueMessageVo $message
     * @param array $receiveUsers
     * @return bool
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function push_queue_message(QueueMessageVo $message, array $receiveUsers = []): bool
    {
        return container()
            ->get(SystemQueueLogService::class)
            ->pushMessage($message, $receiveUsers);
    }
}

if (! function_exists('add_queue')) {
    /**
     * 添加任务到队列
     * @param AmqpQueueVo $amqpQueueVo
     * @return bool
     * @throws Throwable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function add_queue(AmqpQueueVo $amqpQueueVo): bool
    {
        return container()
            ->get(SystemQueueLogService::class)
            ->addQueue($amqpQueueVo);
    }
}