<?php

namespace App\Filament\Resources\ClassRoomResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $relationship): string
    {
        return 'Daftar Siswa';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('roll_number')
                    ->label('Nomor Absen')
                    ->numeric()
                    ->helperText('Kosongkan untuk otomatis berurut sesuai urutan penambahan')
                    ->nullable(),
                Forms\Components\TextInput::make('nis')
                    ->label('NIS')
                    ->required()
                    ->unique(table: 'students', column: 'nis', ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Siswa')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'M' => 'Laki-laki',
                        'F' => 'Perempuan',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('roll_number')
                    ->label('No. Absen')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'M' => 'Laki-laki',
                        'F' => 'Perempuan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'M' => 'info',
                        'F' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d F Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Siswa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
