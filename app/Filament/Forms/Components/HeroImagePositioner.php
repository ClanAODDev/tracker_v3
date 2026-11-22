<?php

namespace App\Filament\Forms\Components;

use App\Models\Division;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HeroImagePositioner extends Field
{
    public const PATTERN = '/^(100|[1-9]?\d)% (100|[1-9]?\d)%$/';

    protected string $view = 'filament.forms.components.hero-image-positioner';

    protected string $imageField = 'hero_image';

    protected function setUp(): void
    {
        parent::setUp();

        $this->nullable()
            ->rule('regex:' . self::PATTERN)
            ->visible(fn (Get $get) => filled($get($this->imageField)));
    }

    public static function resetWhenImageReplaced(string $positionField): Closure
    {
        return function (Set $set, mixed $state) use ($positionField): void {
            $files = Arr::wrap($state);

            if ($files === [] || collect($files)->contains(fn ($file) => $file instanceof UploadedFile)) {
                $set($positionField, null);
            }
        };
    }

    public function imageField(string $field): static
    {
        $this->imageField = $field;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        $state = $this->evaluate(fn (Get $get) => $get($this->imageField));
        $file  = is_array($state) ? Arr::first($state) : $state;

        if ($file instanceof TemporaryUploadedFile) {
            return $file->isPreviewable() ? $file->temporaryUrl() : null;
        }

        return is_string($file) && $file !== '' ? Storage::disk('public')->url($file) : null;
    }

    public function getDivisionName(): string
    {
        $record = $this->getRecord();

        return $record instanceof Division ? $record->name : 'Division';
    }

    public function getDivisionIcon(): ?string
    {
        $record = $this->getRecord();

        return $record instanceof Division ? $record->getLogoPath() : null;
    }
}
