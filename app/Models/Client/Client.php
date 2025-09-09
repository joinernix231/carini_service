<?php

namespace App\Models\Client;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    public const CLIENT_TYPES = ['Natural', 'Jurídico'];
    public const DOCUMENT_TYPES = ['CC', 'CE', 'CI', 'PASS', 'NIT'];

    protected $table = 'clients';

    protected $fillable = [
        'identifier',
        'name',
        'legal_representative',
        'address',
        'city',
        'department',
        'user_id',
        'email',
        'phone',
        'client_type',
        'document_type',
    ];

    public static array $rules = [
        'identifier' => 'string|max:20',
        'name' => 'string|max:255',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'email' => 'nullable|string',
        'email.*' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'client_type' => 'nullable|string|in:Natural,Jurídico',
        'document_type' => 'nullable|string|in:CC,CE,CI,PASS,NIT',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
