<?php

namespace App\Events\Maintenance;

use App\Models\Maintenance\SparePartSuggestion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SparePartSuggestionCreated
{
    use Dispatchable, SerializesModels;

    public SparePartSuggestion $sparePartSuggestion;

    public function __construct(SparePartSuggestion $sparePartSuggestion)
    {
        $this->sparePartSuggestion = $sparePartSuggestion;
    }
}
