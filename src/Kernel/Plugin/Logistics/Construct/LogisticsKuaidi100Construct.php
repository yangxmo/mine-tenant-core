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
namespace Mine\Kernel\Plugin\Logistics\Construct;

interface LogisticsKuaidi100Construct
{
    /**
     * 实时快递查询接口.
     * @param string $code 快递编码
     * @param string $num 快递单号
     * @param null|string $phone 手机号码
     * @return mixed
     */
    public function query(string $code, string $num, string $phone = null, callable $function = null);
}
