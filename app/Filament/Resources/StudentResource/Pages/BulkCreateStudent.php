<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class BulkCreateStudent extends Page
{
    protected static string $resource = StudentResource::class;

    protected static string $view = 'filament.resources.student-resource.pages.bulk-create-student';

    protected static ?string $title = 'Tambah Multiple Siswa';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'class_room_id' => null,
            'students' => [
                ['nis' => '', 'name' => '', 'gender' => ''],
                ['nis' => '', 'name' => '', 'gender' => ''],
                ['nis' => '', 'name' => '', 'gender' => ''],
                ['nis' => '', 'name' => '', 'gender' => ''],
                ['nis' => '', 'name' => '', 'gender' => ''],
            ]
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->heading('Pilih Kelas')
                    ->description('Pilih kelas untuk semua siswa yang akan diinput')
                    ->schema([
                        Forms\Components\Select::make('class_room_id')
                            ->label('Kelas')
                            ->options(function () {
                                return \App\Models\ClassRoom::query()
                                    ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                    ->orderBy('grade_level')
                                    ->orderBy('name')
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->helperText('Semua siswa akan dimasukkan ke kelas yang sama'),
                    ])
                    ->columns(1),
                
                Forms\Components\Section::make()
                    ->heading('Data Siswa')
                    ->description('Input data siswa (NIS, Nama, Jenis Kelamin)')
                    ->schema([
                        Forms\Components\Repeater::make('students')
                            ->label('')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('nis')
                                            ->label('NIS')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('students', 'nis')
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Siswa')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                        Forms\Components\Select::make('gender')
                                            ->label('Jenis Kelamin')
                                            ->options([
                                                'M' => 'Laki-laki',
                                                'F' => 'Perempuan',
                                            ])
                                            ->required()
                                            ->columnSpan(1),
                                    ])
                            ])
                            ->reorderable(false)
                            ->addActionLabel('Tambah Siswa')
                            ->minItems(1)
                            ->maxItems(30)
                            ->defaultItems(5)
                            ->collapsible()
                            ->columns(1)
                            ->visible(fn($get) => !empty($get('class_room_id')))
                    ])
                    ->visible(fn($get) => !empty($get('class_room_id')))
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        
        // Validasi kelas harus dipilih
        if (empty($data['class_room_id'])) {
            Notification::make()
                ->title('Gagal!')
                ->body('Silakan pilih kelas terlebih dahulu')
                ->danger()
                ->send();
            return;
        }
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($data['students'] as $studentData) {
            try {
                // Check if all required fields are filled
                if (empty($studentData['nis']) || empty($studentData['name']) || 
                    empty($studentData['gender'])) {
                    continue; // Skip empty rows
                }

                // Check duplicate NIS
                if (Student::where('nis', $studentData['nis'])->exists()) {
                    $errorCount++;
                    $errors[] = "NIS {$studentData['nis']} sudah terdaftar";
                    continue;
                }

                // Add class_room_id from the main form
                $studentData['class_room_id'] = $data['class_room_id'];

                Student::create($studentData);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Error pada siswa {$studentData['name']}: " . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            Notification::make()
                ->title('Berhasil!')
                ->body("Berhasil menambahkan {$successCount} siswa." . 
                       ($errorCount > 0 ? " {$errorCount} gagal." : ''))
                ->success()
                ->send();
        }

        if ($errorCount > 0 && $successCount === 0) {
            Notification::make()
                ->title('Gagal!')
                ->body(implode("\n", array_slice($errors, 0, 3)))
                ->danger()
                ->send();
            return;
        }

        $this->redirect(StudentResource::getUrl('index'));
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('create')
                ->label('Simpan Semua')
                ->submit('create')
                ->color('primary'),
            Forms\Components\Actions\Action::make('cancel')
                ->label('Batal')
                ->url(StudentResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
