<?php

namespace App\Models\Client;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'client_id',
        'nombre_contacto',
        'correo',
        'telefono',
        'direccion',
        'cargo',
    ];

    public static array $rules = [
        'client_id' => 'required|integer|exists:clients,id',
        'nombre_contacto' => 'required|string|max:255',
        'correo' => 'nullable|email|max:255',
        'telefono' => 'nullable|string|max:20',
        'direccion' => 'nullable|string|max:255',
        'cargo' => 'nullable|string|max:255',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
