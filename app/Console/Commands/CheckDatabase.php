<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use App\Models\CourseDuration;
use App\Models\Accommodation;

class CheckDatabase extends Command
{
    protected $signature = 'check:database';
    protected $description = 'Check available courses, durations, and accommodations';

    public function handle()
    {
        $this->info('=== Available Courses ===');
        $courses = Course::select('id', 'name')->get();
        foreach ($courses as $course) {
            $this->info($course->id . ': ' . $course->name);
        }

        $this->info('\n=== Available Course Durations ===');
        $durations = CourseDuration::select('id', 'weeks')->get();
        foreach ($durations as $duration) {
            $this->info($duration->id . ': ' . $duration->weeks . ' weeks');
        }

        $this->info('\n=== Available Accommodations ===');
        $accommodations = Accommodation::select('id', 'name')->get();
        foreach ($accommodations as $accommodation) {
            $this->info($accommodation->id . ': ' . $accommodation->name);
        }
    }
}