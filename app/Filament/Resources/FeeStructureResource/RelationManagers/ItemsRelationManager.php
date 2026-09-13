<?php

namespace App\Filament\Resources\FeeStructureResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fee_type_id')->relationship('feeType', 'name')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('amount')->required()->numeric()->minValue(0),
                Forms\Components\DatePicker::make('due_date')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('feeType.name')
            ->columns([
                Tables\Columns\TextColumn::make('feeType.name')->label('Fee type')->searchable(),
                Tables\Columns\TextColumn::make('amount')->numeric(),
                Tables\Columns\TextColumn::make('due_date')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
