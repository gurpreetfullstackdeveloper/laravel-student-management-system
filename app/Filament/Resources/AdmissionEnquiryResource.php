<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionEnquiryResource\Pages;
use App\Filament\Resources\AdmissionEnquiryResource\RelationManagers;
use App\Models\AdmissionEnquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdmissionEnquiryResource extends Resource
{
    protected static ?string $model = AdmissionEnquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Admissions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('student_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('parent_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->maxLength(255),
                Forms\Components\TextInput::make('class_applied_for')->required()->maxLength(255),
                Forms\Components\Textarea::make('message')->columnSpanFull(),
                Forms\Components\Select::make('status')->options(['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'rejected' => 'Rejected'])->required()->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student_name')->searchable(),
                Tables\Columns\TextColumn::make('parent_name')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('class_applied_for')->label('Class'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
            'index' => Pages\ListAdmissionEnquiries::route('/'),
            'create' => Pages\CreateAdmissionEnquiry::route('/create'),
            'edit' => Pages\EditAdmissionEnquiry::route('/{record}/edit'),
        ];
    }    
}
