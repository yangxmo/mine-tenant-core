<?php

declare(strict_types=1);

namespace Mine\Mapper;

use Mine\Model\TenantModel;
use Mine\Abstracts\AbstractMapper;

/**
 * Class SystemCorpMapper
 * @package App\System\Mapper
 */
class TenantMapper extends AbstractMapper
{
    /**
     * @var TenantModel
     */
    public $model;

    public function assignModel(): void
    {
        $this->model = TenantModel::class;
    }
}
