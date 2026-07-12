<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\AcademicEvent;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

class AcademicCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.academic-calendar';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Kalender Akademik';
    protected static ?string $title = 'Kalender Akademik';
    
    public $currentMonth;
    public $currentYear;
    
    public function mount()
    {
        $now = now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }
    
    public function previousMonth()
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }

    public function nextMonth()
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function goToToday()
    {
        $now = now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->model(AcademicEvent::class)
                ->label('Tambah Event')
                ->form([
                    TextInput::make('title')
                        ->label('Nama Kegiatan')
                        ->required()
                        ->maxLength(255),
                    Select::make('type')
                        ->label('Jenis')
                        ->options([
                            'holiday' => 'Hari Libur',
                            'exam' => 'Ujian',
                            'meeting' => 'Rapat',
                            'activity' => 'Kegiatan',
                            'other' => 'Lainnya',
                        ])
                        ->required(),
                    Toggle::make('is_range')
                        ->label('Rentang beberapa hari?')
                        ->live(),
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->hidden(fn (Get $get) => ! $get('is_range'))
                        ->required(fn (Get $get) => $get('is_range')),
                    Textarea::make('description')
                        ->label('Keterangan')
                        ->rows(4),
                ])
                ->using(function (array $data): AcademicEvent {
                    $data['created_by'] = auth()->id();
                    if (empty($data['is_range'])) {
                        $data['end_date'] = $data['start_date'];
                    }
                    unset($data['is_range']);
                    return AcademicEvent::create($data);
                })
        ];
    }
    
    public function editEventAction(): Action
    {
        return Action::make('editEvent')
            ->label('Edit Event')
            ->modalHeading('Edit Event')
            ->form([
                TextInput::make('title')
                    ->label('Nama Kegiatan')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'holiday' => 'Hari Libur',
                        'exam' => 'Ujian',
                        'meeting' => 'Rapat',
                        'activity' => 'Kegiatan',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Toggle::make('is_range')
                    ->label('Rentang beberapa hari?')
                    ->live(),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->hidden(fn (Get $get) => ! $get('is_range'))
                    ->required(fn (Get $get) => $get('is_range')),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(4),
            ])
            ->fillForm(function (array $arguments): array {
                $event = AcademicEvent::findOrFail($arguments['event']);
                return [
                    'title' => $event->title,
                    'type' => $event->type,
                    'is_range' => $event->start_date->format('Y-m-d') !== $event->end_date->format('Y-m-d'),
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                    'description' => $event->description,
                ];
            })
            ->action(function (array $data, array $arguments): void {
                $event = AcademicEvent::findOrFail($arguments['event']);
                if (empty($data['is_range'])) {
                    $data['end_date'] = $data['start_date'];
                }
                unset($data['is_range']);
                $event->update($data);
            });
    }

    public function deleteEvent(int $id): void
    {
        AcademicEvent::find($id)?->delete();
    }

    public function getCalendarDaysProperty()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startDay = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $endDay = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $startDay->copy();
        
        $events = AcademicEvent::where(function($q) use ($startDay, $endDay) {
            $q->whereBetween('start_date', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
              ->orWhereBetween('end_date', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
              ->orWhere(function($sq) use ($startDay, $endDay) {
                  $sq->where('start_date', '<', $startDay->format('Y-m-d'))
                     ->where('end_date', '>', $endDay->format('Y-m-d'));
              });
        })->get();

        while ($current->lte($endDay)) {
            $dateStr = $current->format('Y-m-d');
            
            // Find events for this day
            $dayEvents = $events->filter(function($event) use ($dateStr) {
                return $dateStr >= $event->start_date->format('Y-m-d')
                    && $dateStr <= $event->end_date->format('Y-m-d');
            })->values()->toArray();
            
            $days[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'isCurrentMonth' => $current->month == $this->currentMonth,
                'isToday' => $current->isToday(),
                'isWeekend' => $current->isWeekend(),
                'events' => $dayEvents,
            ];
            $current->addDay();
        }

        return $days;
    }
}
