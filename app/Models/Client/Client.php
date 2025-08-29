<?php

namespace App\Models\Client;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'identifier' => 'required|string|max:20',
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'email' => 'nullable|string',
        'email.*' => 'email',
        'phone' => 'nullable|string|max:20',
    ];

    public function user(): hasOne
    {
        return $this->hasOne(User::class);
    }
}
