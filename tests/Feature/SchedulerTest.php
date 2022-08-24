<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\Event;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    /** @test */
    function it_schedules_update_crypto_prices_every_minute()
    {
        $schedule = app()->make(Schedule::class);

        $events = collect($schedule->events())
            ->filter(function (Event $event) {
                return stripos($event->command, 'crypto:update');
            });

        $this->assertCount(1, $events);
    }
}
