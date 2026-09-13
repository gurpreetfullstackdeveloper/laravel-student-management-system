<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeePaymentResource\Pages;
use App\Filament\Resources\FeePaymentResource\RelationManagers;
use App\Models\FeePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeePaymentResource extends Resource
{
    protected static ?string $model = FeePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Fees';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_fee_id')->relationship('studentFee', 'id')->searchable()->preload()->required()->label('Student fee'),
                Forms\Components\TextInput::make('amount_paid')->required()->numeric()->minValue(0.01),
                Forms\Components\DateTimePicker::make('paid_at')->required()->default(now()),
                Forms\Components\Select::make('payment_method')->options(['cash' => 'Cash', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank transfer', 'online' => 'Online'])->required()->native(false),
                Forms\Components\TextInput::make('reference_no')->maxLength(255),
                Forms\Components\TextInput::make('receipt_no')->required()->unique(ignoreRecord: true)->default(fn () => 'RCPT-'.now()->format('YmdHis').'-'.random_int(100, 999)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('studentFee.student.admission_no')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('receipt_no')->searchable(),
                Tables\Columns\TextColumn::make('amount_paid')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->badge(),
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
            'index' => Pages\ListFeePayments::route('/'),
            'create' => Pages\CreateFeePayment::route('/create'),
            'edit' => Pages\EditFeePayment::route('/{record}/edit'),
        ];
    }    
}
