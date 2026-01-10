<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('finance:generate-recurring')->daily();
