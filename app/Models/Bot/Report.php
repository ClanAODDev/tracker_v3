<?php

namespace App\Models\Bot;

interface Report
{
    public function handle(): string;
}
