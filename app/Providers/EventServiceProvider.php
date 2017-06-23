<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
    ];

    
    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //绑定模型的Observer
        $this->registerModelObservers();
    }

    /**
     * 绑定模型的Observer
     */
    protected function registerModelObservers()
    {
        \App\Models\Contract::observe(
            app(\App\Observers\ContractObserver::class)
        );
        \App\Models\BackcashInvoiceModel::observe(
                app(\App\Observers\BackcashObserver::class)
        );
        \App\Models\Badcash::observe(
                app(\App\Observers\BadcashObserver::class)
        );
        \App\Models\ExpensesDeliveyModel::observe(
                app(\App\Observers\ExpensesObserver::class)
        );
        \App\Models\InvoiceDeliveyModel::observe(
                app(\App\Observers\InvoiceObserver::class)
        );
        \App\Models\BusinessTeamModel::observe(
                app(\App\Observers\BusinessTeamObserver::class)
        );
    }

}
