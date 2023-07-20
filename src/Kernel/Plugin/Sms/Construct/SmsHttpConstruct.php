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

interface SmsHttpConstruct
{
    /**
     * Retrieve a user by the given credentials.
     */
    public function sendSms(string $mobile, string $message, string $code = null, callable $function = null);

    /**
     * Validate a user against the given credentials.
     */
    public function checkSmsCode(string $mobile, string $code): bool;
}
