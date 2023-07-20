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
namespace Mine\Middlewares;

use Mine\Kernel\Tenant\Tenant;
use Exception;
use Hyperf\Context\Context;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TenantMiddleware implements MiddlewareInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $corpId = $request->getHeaderLine('TenantId') ?? null;

        if ($request->getMethod() == 'OPTIONS') {
            return $handler->handle($request);
        }
        try {
            // 获取配置中心的所有企业
            $tenantNoArr = \Hyperf\Config\config('corp_no');

            // 判定是否所有企业中有没有当前这个企业
            if (empty($corpId) || ! in_array($corpId, $tenantNoArr)) {
                return container()->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->json([
                    'success' => false,
                    'message' => t('tenant.corp_notfound'),
                    'code' => 200,
                    'data' => [],
                ]);
            }
            Context::set('tenant_id', $corpId);

            Tenant::instance()->init($corpId);
        } catch (Exception $e) {
            return container()->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 200,
                'data' => [],
            ]);
        }

        return $handler->handle($request);
    }
}
