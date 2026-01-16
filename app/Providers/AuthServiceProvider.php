<?php

namespace App\Providers;

use App\Models\Factura;
use App\Models\FacturaPago;
use App\Policies\FacturaPolicy;
use App\Policies\FacturaPagoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Factura::class => FacturaPolicy::class,
        FacturaPago::class => FacturaPagoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
