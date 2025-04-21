<?php

namespace App\Models\Client;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{

    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'identifier',
        'business_name',
        'legal_representative',
        'address',
        'city',
        'contact_emails',
        'phone',
        'client_type',
    ];

    protected $casts = [
        'contact_emails' => 'array',
    ];

    public static array $rules = [
        'identifier' => 'required|string|max:20|unique:clients,identifier',
        'business_name' => 'required|string|max:255',
        'legal_representative' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'email' => 'nullable|array',
        'email.*' => 'email',
        'phone' => 'nullable|string|max:20',
        'client_type' => 'nullable|string|max:100',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
