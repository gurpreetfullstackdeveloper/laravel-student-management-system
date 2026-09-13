<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentFeeResource\Pages;
use App\Filament\Resources\StudentFeeResource\RelationManagers;
use App\Models\StudentFee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentFeeResource extends Resource
{
    protected static ?string $model = StudentFee::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Fees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')->relationship('student', 'admission_no')->searchable()->preload()->required(),
                Forms\Components\Select::make('fee_structure_item_id')->relationship('feeStructureItem', 'id')->searchable()->preload()->required()->label('Fee item'),
                Forms\Components\Select::make('academic_year_id')->relationship('academicYear', 'name')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('amount_payable')
                    ->required()
                    ->numeric()->minValue(0),
                Forms\Components\TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('discount_reason')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.admission_no')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('feeStructureItem.feeType.name')->label('Fee type'),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic year'),
                Tables\Columns\TextColumn::make('amount_payable')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_reason')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outstanding_amount')->label('Outstanding')->money('USD'),
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
            'index' => Pages\ListStudentFees::route('/'),
            'create' => Pages\CreateStudentFee::route('/create'),
            'edit' => Pages\EditStudentFee::route('/{record}/edit'),
        ];
    }    
}
