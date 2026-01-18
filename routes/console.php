<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('finance:generate-recurring')->daily();

// Backups - Run every 7 days (Sunday at 3 AM)
Schedule::command('backup:run')->weeklyOn(0, '03:00');
Schedule::command('backup:clean')->weeklyOn(0, '04:00');
