<?php

namespace App\Filament\Support;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ReferenceDataResourceSupport
{
    /**
     * @param  array<string, mixed>  $options
     */
    public static function form(Form $form, array $options = []): Form
    {
        $schema = [];

        if ($options['code'] ?? false) {
            $schema[] = Forms\Components\TextInput::make('code')->required()->maxLength(80);
        }

        if ($options['name'] ?? true) {
            $schema[] = Forms\Components\TextInput::make('name')->required()->maxLength(255);
        }

        if ($options['slug'] ?? true) {
            $schema[] = Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255);
        }

        if ($options['label'] ?? false) {
            $schema[] = Forms\Components\TextInput::make('label')->required()->maxLength(255);
        }

        if (isset($options['value_options']) && is_array($options['value_options'])) {
            $schema[] = Forms\Components\Select::make('value')
                ->options($options['value_options'])
                ->required()
                ->native(false);
        }

        if (isset($options['tone_options']) && is_array($options['tone_options'])) {
            $schema[] = Forms\Components\Select::make('tone')
                ->options($options['tone_options'])
                ->required()
                ->native(false);
        }

        if ($options['office_type'] ?? false) {
            $schema[] = Forms\Components\TextInput::make('office_type')->maxLength(255);
        }

        if ($options['website_url'] ?? false) {
            $schema[] = Forms\Components\TextInput::make('website_url')->url()->maxLength(255);
        }

        if ($options['description'] ?? true) {
            $schema[] = Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull();
        }

        if ($options['notes'] ?? false) {
            $schema[] = Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull();
        }

        if ($options['sort_order'] ?? true) {
            $schema[] = Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->required();
        }

        $schema[] = Forms\Components\Toggle::make('is_active')->default(true)->required();

        return $form->schema([
            Forms\Components\Section::make('Reference data')
                ->columns(2)
                ->schema($schema),
        ]);
    }

    /**
     * @param  list<string>  $columns
     */
    public static function table(Table $table, array $columns = ['name', 'slug']): Table
    {
        $tableColumns = [];

        foreach ($columns as $column) {
            $tableColumns[] = Tables\Columns\TextColumn::make($column)
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: in_array($column, ['slug', 'description', 'notes', 'website_url'], true));
        }

        $tableColumns[] = Tables\Columns\IconColumn::make('is_active')->boolean();
        $tableColumns[] = Tables\Columns\TextColumn::make('updated_at')
            ->dateTime(timezone: 'Asia/Manila')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->columns($tableColumns)
            ->defaultSort(in_array('sort_order', $columns, true) ? 'sort_order' : $columns[0])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @param  iterable<object>  $cases
     * @return array<string, string>
     */
    public static function enumOptions(iterable $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $value = $case->value ?? null;

            if (! is_string($value)) {
                continue;
            }

            $options[$value] = method_exists($case, 'label')
                ? $case->label()
                : str($value)->replace('_', ' ')->headline()->toString();
        }

        return $options;
    }
}
