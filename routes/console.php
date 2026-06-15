<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('billing:monthly-generate')->monthlyOn(1, '00:30')->withoutOverlapping(); // প্রতি মাসের ১ তারিখ রাত ১২:৩০ এ চলবে
Schedule::command('billing:check-overdue')->daily(); // প্রতিদিন মধ্যরাত ১২:০০ তে চলবে