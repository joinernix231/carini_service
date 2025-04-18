<?php

namespace App\Models\Device;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Device extends Model
{
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_device');
    }
}
