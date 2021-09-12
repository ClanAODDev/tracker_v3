<?php

namespace App\Solutions;

use Facade\IgnitionContracts\RunnableSolution;
use Illuminate\Support\Facades\Artisan;

class SeederMissingSolution implements RunnableSolution
{
    public function getSolutionTitle(): string
    {
        return 'Run database seeders';
    }

    public function getSolutionActionDescription(): string
    {
        return 'Run database seeders using `php artisan db:seed`.';
    }

    public function getRunButtonText(): string
    {
        return 'Run seeders';
    }

    public function getSolutionDescription(): string
    {
        return '';
    }

    public function getRunParameters(): array
    {
        return [];
    }

    public function run(array $parameters = [])
    {
        Artisan::call('db:seed');
    }

    public function getDocumentationLinks(): array
    {
        return [];
    }
}
