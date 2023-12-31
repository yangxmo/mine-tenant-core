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
namespace Mine\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 设置某个万能通用接口状态，true 允许使用，false 禁止使用
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RemoteState extends AbstractAnnotation
{
    /**
     * @param bool $state 状态
     */
    public function __construct(public bool $state = true) {}
}