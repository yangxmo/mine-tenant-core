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

declare(strict_types=1);
namespace Mine\Crontab;

use Carbon\Carbon;
use Hyperf\Di\Annotation\Inject;

use function Hyperf\Coroutine\co;

class MineCrontabStrategy
{
    /**
     * MineCrontabManage
     */
    #[Inject]
    protected MineCrontabManage $mineCrontabManage;

    /**
     * MineExecutor
     */
    #[Inject]
    protected MineExecutor $executor;

    /**
     * @param MineCrontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function dispatch(MineCrontab $crontab)
    {
        co(function() use($crontab) {
            if ($crontab->getExecuteTime() instanceof Carbon) {
                $wait = $crontab->getExecuteTime()->getTimestamp() - time();
                $wait > 0 && \Swoole\Coroutine::sleep($wait);
                $this->executor->execute($crontab);
            }
        });
    }

    /**
     * 执行一次
     * @param MineCrontab $crontab
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function executeOnce(MineCrontab $crontab)
    {
        co(function() use($crontab) {
            $this->executor->execute($crontab);
        });
    }
}