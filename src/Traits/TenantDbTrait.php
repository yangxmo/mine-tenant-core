<?php

namespace Mine\Traits;

use Mine\Kernel\Tenant\Tenant;

trait TenantDbTrait
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = Tenant::instance()->getId();
    }
}