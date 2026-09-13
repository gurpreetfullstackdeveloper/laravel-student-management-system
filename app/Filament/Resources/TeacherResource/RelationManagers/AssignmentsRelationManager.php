<?php

namespace App\Filament\Resources\TeacherResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Teaching assignments';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('academic_year_id')
                ->relationship('academicYear', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('subject_id')
                ->relationship('subject', 'name')
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
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic year')->sortable(),
                Tables\Columns\TextColumn::make('subject.name')->label('Subject')->searchable(),
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class'),
                Tables\Columns\TextColumn::make('section.name')->label('Section'),
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