<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAttendanceResource\Pages;
use App\Filament\Resources\TeacherAttendanceResource\RelationManagers;
use App\Models\TeacherAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeacherAttendanceResource extends Resource
{
    protected static ?string $model = TeacherAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Kehadiran Guru';

    protected static ?string $modelLabel = 'Kehadiran Guru';

    protected static ?string $pluralModelLabel = 'Kehadiran Guru';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->unique(
                        table: 'teacher_attendances',
                        column: 'date',
                        modifyRuleUsing: fn (\Illuminate\Validation\Rules\Unique $rule, \Filament\Forms\Get $get) => $rule->where('teacher_id', $get('teacher_id')),
                        ignoreRecord: true,
                    )
                    ->validationMessages([
                        'unique' => 'Data absensi untuk guru ini pada tanggal tersebut sudah ada.',
                    ]),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'HADIR' => 'Hadir',
                        'TIDAK_HADIR' => 'Tidak Hadir',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Contoh: Sakit, izin, dll')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(Tables\Actions\ViewAction::class)
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'HADIR' => 'success',
                        'TIDAK_HADIR' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'HADIR' => 'Hadir',
                        'TIDAK_HADIR' => 'Tidak Hadir',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'HADIR' => 'Hadir',
                        'TIDAK_HADIR' => 'Tidak Hadir',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Hingga Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $q, $date): Builder => $q->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $q, $date): Builder => $q->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTeacherAttendances::route('/'),
            'create' => Pages\CreateTeacherAttendance::route('/create'),
            'edit' => Pages\EditTeacherAttendance::route('/{record}/edit'),
        ];
    }
}
