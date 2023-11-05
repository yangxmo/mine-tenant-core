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

declare(strict_types = 1);
namespace Mine\Annotation\Api;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 用户登录验证。
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class MApiAuth extends AbstractAnnotation
{
    /**
     * @param string $scene 场景名
     */
    public function __construct(public string $scene = 'api') {}
}