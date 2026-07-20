<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicYearResource\Pages;
use App\Filament\Resources\AcademicYearResource\RelationManagers;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Tahun Ajaran';

    protected static ?string $modelLabel = 'Tahun Ajaran';

    protected static ?string $pluralModelLabel = 'Tahun Ajaran';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tahun Ajaran')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tahun Ajaran')
                            ->placeholder('2024/2025')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai (Keseluruhan)')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai (Keseluruhan)')
                            ->required()
                            ->after('start_date'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(false)
                            ->helperText('Hanya satu tahun ajaran yang boleh aktif'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Pengaturan Semester')
                    ->schema([
                        Forms\Components\Repeater::make('semesters')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Semester')
                                    ->options([
                                        '1' => 'Ganjil',
                                        '2' => 'Genap',
                                    ])
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Tanggal Mulai')
                                    ->required(),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->after('start_date'),
                            ])
                            ->minItems(2)
                            ->maxItems(2)
                            ->columns(3)
                            ->defaultItems(2)
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    $s1 = collect($value)->firstWhere('type', '1');
                                    $s2 = collect($value)->firstWhere('type', '2');
                                    if ($s1 && $s2 && !empty($s1['end_date']) && !empty($s2['start_date'])) {
                                        if (strtotime($s1['end_date']) >= strtotime($s2['start_date'])) {
                                            $fail('Tanggal selesai Semester Ganjil harus sebelum tanggal mulai Semester Genap.');
                                        }
                                    }
                                };
                            })
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
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
            'index' => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
