<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Kelola Admin';

    protected static ?string $modelLabel = 'Admin';

    protected static ?string $pluralModelLabel = 'Admin';

    protected static ?string $navigationGroup = 'Manajemen User';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'admins';

    /**
     * Scope the query to only show users with the 'admin' role.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun Admin')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->helperText(fn (string $operation): string =>
                                $operation === 'edit'
                                    ? 'Kosongkan jika tidak ingin mengubah password.'
                                    : 'Minimal 8 karakter.'
                            ),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                        if (User::role('admin')->count() <= 1) {
                            Notification::make()
                                ->title('Tidak Dapat Menghapus')
                                ->body('Tidak dapat menghapus admin terakhir di sistem. Minimal harus ada satu admin.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                            $totalAdmins = User::role('admin')->count();
                            $deleteCount = $records->count();

                            if ($totalAdmins - $deleteCount < 1) {
                                Notification::make()
                                    ->title('Tidak Dapat Menghapus')
                                    ->body('Operasi ini akan menghapus semua admin di sistem. Minimal harus ada satu admin tersisa.')
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
