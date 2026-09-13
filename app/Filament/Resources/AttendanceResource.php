<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Attendance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')->relationship('student', 'admission_no')->searchable()->preload()->required(),
                Forms\Components\Select::make('class_id')->relationship('schoolClass', 'name')->searchable()->preload()->required()->label('Class'),
                Forms\Components\Select::make('section_id')->relationship('section', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('academic_year_id')->relationship('academicYear', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('recorded_by')->relationship('recorder', 'name')->searchable()->preload()->required(),
                Forms\Components\DatePicker::make('date')->required(),
                Forms\Components\Select::make('status')->options(['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'leave' => 'Leave'])->required()->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.admission_no')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('schoolClass.name')->label('Class'),
                Tables\Columns\TextColumn::make('section.name')->label('Section'),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('recorder.name')->label('Recorded by'),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }    
}
