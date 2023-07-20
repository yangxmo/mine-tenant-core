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
namespace Mine\Kernel\Plugin\Logistics\Handler;

use Mine\Kernel\Plugin\Logistics\Construct\AbstructLogistics;
use Mine\Kernel\Plugin\Logistics\Construct\LogisticsKuaidi100Construct;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;

class Kuaidi100Handler extends AbstructLogistics implements LogisticsKuaidi100Construct
{
    /**
     * 实时快递查询接口.
     * @param string $code 快递编码
     * @param string $num 快递单号
     * @param null|string $phone 手机号码
     * @throws GuzzleException
     */
    public function query(string $code, string $num, string $phone = null, callable $function = null)
    {
        try {
            if (is_null($function)) {
                return null;
            }
            $customer = $this->config['customer'];
            $key = $this->config['key'];
            $params = json_encode(['com' => $code, 'num' => $num, 'phone' => $phone]);
            // 签名用于验证身份，按params + key + customer 的顺序进行MD5加密（注意加密后字符串一定要转32位大写）
            $sign = strtoupper(md5($params . $key . $customer));
            $postData = [
                'customer' => $customer, // 授权码
                'sign' => $sign,
                'param' => $params,
            ];
            $path = '/poll/query.do';
            $responseContent = $this->clientFactory->request('post', $path, [
                'form_params' => $postData,
            ]);
            if ($responseContent->getStatusCode() == 200) {
                $result = $responseContent->getBody()->getContents();
                $result = json_decode($result, true);
            } else {
                $result = [];
            }
            return $function($result);
        } catch (Exception $exception) {
            return $function(['code' => $exception->getCode(), 'message' => $exception->getMessage()]);
        }
    }

    public function initInstance(): void
    {
        $host = $this->config['host'] ?? [];
        $clientFactory = $this->container->get(ClientFactory::class);
        $this->clientFactory = $clientFactory->create(['base_uri' => $host, 'debug' => true]);
    }
}
