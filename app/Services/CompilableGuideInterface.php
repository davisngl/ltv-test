<?php

namespace App\Services;

use Illuminate\Support\Collection;

interface CompilableGuideInterface
{
    /**
     * Create a collection of broadcast airings for the day.
     */
    public function compile(): Collection;
}
