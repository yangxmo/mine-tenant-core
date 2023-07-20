<?php

namespace Mine\Kernel\Tenant;

use Hyperf\Support\Traits\StaticInstance;

class Tenant
{
    use StaticInstance;

    public string $tenantId = 'center';

    /**
     * @param string|null $tenantId
     * @return void
     */
    public function init(string $tenantId = null): void
    {
        if (is_null($tenantId)) {
            $tenantId = context_get('tenant_id');
        }

        $this->tenantId = $tenantId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->tenantId;
    }

}