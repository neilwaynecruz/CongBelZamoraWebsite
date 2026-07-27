<?php

namespace App\Support;

use Filament\Forms;
use Filament\Forms\Get;

final class ApprovedMediaForm
{
    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function uploadFields(
        string $directoryConfigKey,
        string $sectionDescription,
    ): array {
        $maxKb = (int) config('belzamora.media.max_upload_kilobytes', 5120);
        $mimes = config('belzamora.media.allowed_mimetypes', ['image/jpeg', 'image/png', 'image/webp']);
        $directory = (string) config('belzamora.media.'.$directoryConfigKey, 'approved-media');
        $maxMbLabel = rtrim(rtrim(number_format($maxKb / 1024, 2, '.', ''), '0'), '.');

        return [
            Forms\Components\Section::make('Approved media file')
                ->description($sectionDescription)
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('media_path')
                        ->label('Approved media file')
                        ->disk('public')
                        ->directory($directory)
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('180')
                        ->openable()
                        ->downloadable()
                        ->acceptedFileTypes(is_array($mimes) ? $mimes : ['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize($maxKb)
                        ->helperText('JPEG, PNG, or WebP only. Maximum '.$maxMbLabel.' MB. Do not upload IDs, medical files, or other sensitive documents.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('caption')
                        ->required(fn (Get $get): bool => filled($get('media_path')))
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('alt_text')
                        ->label('Alt text')
                        ->required(fn (Get $get): bool => filled($get('media_path')))
                        ->helperText('Required for published images. Describe the image for screen readers.')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('rights_note')
                        ->label('Rights and consent note')
                        ->required(fn (Get $get): bool => filled($get('media_path')))
                        ->helperText('Record source rights, consent status, and any publication limits before verification.')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
