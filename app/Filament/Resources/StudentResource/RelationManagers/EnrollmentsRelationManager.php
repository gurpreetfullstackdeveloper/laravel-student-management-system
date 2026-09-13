<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Enrollment history';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('academic_year_id')
                ->relationship('academicYear', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('class_id')
                ->relationship('schoolClass', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('section_id')
                ->relationship('section', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('roll_no')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'promoted' => 'Promoted',
                    'transferred' => 'Transferred',
                    'left' => 'Left',
                ])
                ->required()
                ->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('roll_no')
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic year')->sortable(),
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class'),
                Tables\Columns\TextColumn::make('section.name')->label('Section'),
                Tables\Columns\TextColumn::make('roll_no'),
                Tables\Columns\TextColumn::make('status')->badge(),
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
            ]);
    }
}