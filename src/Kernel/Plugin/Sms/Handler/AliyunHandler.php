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
namespace Mine\Kernel\Plugin\Sms\Handler;

use Mine\Kernel\Plugin\Sms\Construct\AbstructSms;
use Mine\Kernel\Plugin\Sms\Construct\SmsAliyunConstruct;

class AliyunHandler extends AbstructSms implements SmsAliyunConstruct
{
    public function sendSms(string $mobile, string $message, string $code = null, callable $function = null): bool
    {
        // TODO: Implement sendSms() method.
        return false;
    }

    public function checkSmsCode(string $mobile, string $code): bool
    {
        // TODO: Implement checkSmsCode() method.
        return false;
    }

    public function initInstance()
    {
        // TODO: Implement initInstance() method.
    }
}
