<?php

namespace App\Models\Client;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{

    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'identifier',
        'name',
        'legal_representative',
        'address',
        'city',
        'user_id',
        'email',
        'phone',
        'client_type',
    ];

    public static array $rules = [
        'identifier' => 'string|max:20',
        'name' => 'string|max:255',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'email' => 'nullable|string',
        'email.*' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
