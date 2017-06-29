<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Common\Packages\Linker\Linker;

/**
 * Class AppServiceProvider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Linker $linker)
    {
        //配置Linker
        $this->setupLinker($linker);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * 配置Linker
     * Linker是用来生成各类URL的
     *
     * @param Linker $linker
     */
    protected function setupLinker($linker)
    {
        $adpsUrlPrefix = env('ADPS_URL_PREFIX');
        if ($adpsUrlPrefix == '') throw new \RuntimeException('请配置ADPS_URL_PREFIX');
        $linker->set('adps', $adpsUrlPrefix);

        $resourceUrlPrefix = env('RESOURCE_URL_PREFIX');
        if ($resourceUrlPrefix == '') throw new \RuntimeException('请配置RESOURCE_URL_PREFIX');
        $linker->set('resource', $resourceUrlPrefix);

    }
}
