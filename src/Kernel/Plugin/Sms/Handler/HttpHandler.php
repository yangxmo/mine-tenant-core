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
use Mine\Kernel\Plugin\Sms\Construct\SmsHttpConstruct;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Redis\Redis;

class HttpHandler extends AbstructSms implements SmsHttpConstruct
{
    /**
     * @throws GuzzleException
     */
    public function sendSms(string $mobile, string $message, string $code = null, callable $function = null)
    {
        try {
            if (is_null($function)) {
                return null;
            }
            $url = $this->config['path'];
            $url .= '?' . http_build_query(['content' => $message, 'mobile' => $mobile]);
            $responseContent = $this->clientFactory->request('post', $url);
            if ($responseContent->getStatusCode() == 200) {
                $result = $responseContent->getBody()->getContents();
                $result = json_decode($result, true);
                // 发送成功
                if (isset($result['status']) && $result['status'] == 0) {
                    $this->codeManage($code, $mobile, 'Set');
                }
            } else {
                $result = [];
            }
            return $function($result);
        } catch (Exception $exception) {
            return $function(['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }
    }

    /**
     * 检查是否正确.
     */
    public function checkSmsCode(string $mobile, string $code): bool
    {
        return $this->codeManage($code, $mobile, 'Get');
    }

    public function initInstance(): void
    {
        $host = $this->config['host'] ?? [];
        $headers = $this->config['headers'] ?? [];
        $clientFactory = $this->container->get(ClientFactory::class);
        $this->clientFactory = $clientFactory->create(['headers' => $headers, 'base_uri' => $host, 'debug' => true]);
    }

    private function codeManage(string $code, string $mobile, string $actionType = 'Set'): bool
    {
        // 初始化redis实例
        $cacheHandler = is_callable($this->config['cache']) ? call_user_func_array($this->config['cache'], []) : make(Redis::class);
        // 过期时间
        $cacheTtl = $this->config['ttl'];
        // 获取
        $cachePrefix = $this->config['prefix'];
        // 检查
        if ($actionType == 'Set') {
            return (bool) $cacheHandler->set($cachePrefix . $mobile, $code, $cacheTtl);
        }
        $cacheCode = $cacheHandler->get($cachePrefix . $mobile);
        if ($cacheCode === $code) {
            return true;
        }
        return false;
    }
}
