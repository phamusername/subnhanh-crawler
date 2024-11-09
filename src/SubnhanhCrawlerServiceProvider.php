<?php

namespace Ggg3\SubnhanhCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Ggg3\SubnhanhCrawler\Console\CrawlerScheduleCommand;
use Ggg3\Subnhanhcrawler\Option;

class SubnhanhcrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {
        $admin_preflix = config('backpack.base.route_prefix', 'admin');

        config(['plugins' => array_merge(config('plugins', []), [
            'ggg3/subnhanh-crawler' =>
            [
                'name' => 'Ophim Crawler',
                'package_name' => 'hacoidev/subnhanh-crawler',
                'icon' => 'la la-hand-grab-o',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => url($admin_preflix .'/plugin/subnhanh-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => url($admin_preflix .'/plugin/subnhanh-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'subnhanh-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/hacoidev/subnhanh-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['ophim.updaters' => array_merge(config('ophim.updaters', []), [
            [
                'name' => 'Subnhanh Crawler',
                'handler' => 'Ggg3\SubnhanhCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'subnhanh-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('subnhanh-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
