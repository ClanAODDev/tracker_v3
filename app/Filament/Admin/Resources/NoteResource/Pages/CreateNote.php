<?php

namespace App\Filament\Admin\Resources\NoteResource\Pages;

use App\Filament\Admin\Resources\NoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;
}
