<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('email:send:order-review')->dailyAt('10:00');
Schedule::command('email:send:abandoned-cart')->hourly();
Schedule::command('etsy:sync-orders')->hourly();
Schedule::command('journal:generate-post')->dailyAt('09:00');
