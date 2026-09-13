<?php

namespace App\Filament\Widgets;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FoundationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Students', Student::count())
                ->description('Registered student profiles')
                ->icon('heroicon-o-academic-cap'),
            Stat::make('Teachers', Teacher::count())
                ->description('Registered teacher profiles')
                ->icon('heroicon-o-identification'),
            Stat::make('Classes', SchoolClass::count())
                ->description('Standing class labels')
                ->icon('heroicon-o-building-office-2'),
            Stat::make('Current academic year', AcademicYear::where('is_current', true)->value('name') ?? 'Not set')
                ->description('Active academic scope')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}