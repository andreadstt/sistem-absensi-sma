<?php

namespace App\Filament\Resources;

use App\Mail\TeacherApproved;
use App\Mail\TeacherRejected;
use App\Filament\Resources\TeacherRegistrationResource\Pages;
use App\Models\TeacherRegistration;
use App\Models\User;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherRegistrationResource extends Resource
{
    protected static ?string $model = TeacherRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Pendaftaran Guru';

    protected static ?string $modelLabel = 'Pendaftaran Guru';

    protected static ?string $pluralModelLabel = 'Pendaftaran Guru';

    protected static ?string $navigationGroup = 'Manajemen User';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pendaftar')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan dari Pendaftar')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Review Admin')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Catatan Admin')
                            ->rows(3)
                            ->placeholder('Catatan untuk internal atau yang akan dikirim ke pendaftar')
                            ->columnSpanFull(),
                    ])->columns(1)->visible(fn($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Direview Oleh')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Tanggal Review')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pendaftaran')
                    ->modalDescription(fn($record) => "Apakah Anda yakin ingin menyetujui pendaftaran dari {$record->name}? Sistem akan otomatis membuat akun user dan profil guru.")
                    ->action(function (TeacherRegistration $record) {
                        static::approveRegistration($record);
                    })
                    ->visible(fn($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pendaftaran')
                    ->modalDescription(fn($record) => "Apakah Anda yakin ingin menolak pendaftaran dari {$record->name}?")
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Jelaskan alasan penolakan kepada pendaftar'),
                    ])
                    ->action(function (TeacherRegistration $record, array $data) {
                        static::rejectRegistration($record, $data['admin_notes']);
                    })
                    ->visible(fn($record) => $record->status === 'pending'),

                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => false), // Hide for safety
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function approveRegistration(TeacherRegistration $registration)
    {
        // Validasi: Pastikan password ada di record registrasi
        if (empty($registration->password)) {
            Notification::make()
                ->title('Error')
                ->body('Data pendaftaran tidak memiliki password. Pendaftar mungkin menggunakan form versi lama.')
                ->danger()
                ->send();
            return;
        }

        try {
            \DB::beginTransaction();

            // Create user account using the pre-hashed password
            $user = User::create([
                'name' => $registration->name,
                'email' => $registration->email,
                'password' => $registration->password, // Password is already hashed
                'must_change_password' => false, // User has set their own password
            ]);

            // Assign guru role
            $user->assignRole('guru');

            // Create teacher profile (without default_password)
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'name' => $registration->name,
                'nip' => $registration->nip,
                'phone' => $registration->phone,
            ]);

            // Update registration status
            $registration->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            \DB::commit();

            try {
                // Send email without the password
                Mail::to($registration->email)->send(new TeacherApproved(
                    name: $registration->name,
                    email: $registration->email,
                    loginUrl: route('login'),
                ));
            } catch (\Throwable $e) {
                report($e);
            }

            Notification::make()
                ->title('Pendaftaran Disetujui')
                ->body("Akun guru berhasil dibuat untuk {$registration->name}. Guru dapat login menggunakan password yang telah didaftarkan.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            \DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Gagal menyetujui pendaftaran: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static function rejectRegistration(TeacherRegistration $registration, string $reason)
    {
        try {
            // Simpan data yang diperlukan sebelum record dihapus
            $name = $registration->name;
            $email = $registration->email;

            // Update informasi review
            $registration->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Kirim email penolakan
            Mail::to($email)->send(new TeacherRejected(
                name: $name,
                reason: $reason,
                registerUrl: route('teacher.register.create'),
            ));

            // Hapus data agar email dapat digunakan kembali
            $registration->delete();

            Notification::make()
                ->title('Pendaftaran Ditolak')
                ->body("Pendaftaran dari {$name} telah ditolak dan email pemberitahuan berhasil dikirim.")
                ->warning()
                ->send();

        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Terjadi Kesalahan')
                ->body('Gagal mengirim email penolakan: ' . $e->getMessage())
                ->danger()
                ->send();
        };
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherRegistrations::route('/'),
            'view' => Pages\ViewTeacherRegistration::route('/{record}'),
        ];
    }
}
