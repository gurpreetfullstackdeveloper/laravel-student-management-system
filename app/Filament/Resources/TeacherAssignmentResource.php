<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAssignmentResource\Pages;
use App\Filament\Resources\TeacherAssignmentResource\RelationManagers;
use App\Models\TeacherAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = TeacherAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Academics';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('teacher_id')
                    ->relationship('teacher', 'employee_code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Teacher'),
                Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Subject'),
                Forms\Components\Select::make('class_id')
                    ->relationship('schoolClass', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Class'),
                Forms\Components\Select::make('section_id')
                    ->relationship('section', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Section'),
                Forms\Components\Select::make('academic_year_id')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Academic year'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.employee_code')->label('Teacher')->searchable(),
                Tables\Columns\TextColumn::make('subject.name')->label('Subject')->searchable(),
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class')->searchable(),
                Tables\Columns\TextColumn::make('section.name')->label('Section'),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic year')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAssignments::route('/'),
            'create' => Pages\CreateTeacherAssignment::route('/create'),
            'edit' => Pages\EditTeacherAssignment::route('/{record}/edit'),
        ];
    }    
}
