<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;
    protected static ?string $navigationLabel = 'User';
    protected static string | UnitEnum | null $navigationGroup = 'Setting';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label("Name")
                    ->required(),
                TextInput::make("email")
                    ->required()
                    ->label("Email"),
                Select::make('role')
                    ->label('Role')
                    ->options(User::roleOptions())
                    ->required()
                    ->default(User::ROLE_STAFF),
                TextInput::make("password")
                    ->password()
                    ->required()
                    ->hiddenOn("edit")
                    ->minLength(7)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")
                    ->label("Name"),
                TextColumn::make("email")
                    ->label("Email"),
                TextColumn::make("role")
                    ->label("Peran"),
                TextColumn::make("created_at")
                    ->label("Tgl. Bergabung")

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
