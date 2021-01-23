<?php

namespace App\Providers;

use SleepingOwl\Admin\Providers\AdminSectionsServiceProvider as ServiceProvider;

class AdminSectionsServiceProvider extends ServiceProvider
{

    /**
     * @var array
     */
    protected $sections = [
        //\App\User::class => 'App\Http\Sections\Users',
        \App\Models\Shop::class => 'App\Http\Sections\Shop',
        \App\Models\Delivery::class => 'App\Http\Sections\Delivery',
        \App\Models\Order::class => 'App\Http\Sections\Order',
        \App\Models\Supply::class => 'App\Http\Sections\Supply',
    ];

    /**
     * Register sections.
     *
     * @param \SleepingOwl\Admin\Admin $admin
     * @return void
     */
    public function boot(\SleepingOwl\Admin\Admin $admin)
    {
    	//

        parent::boot($admin);
    }
}
