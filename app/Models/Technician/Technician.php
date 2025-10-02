<?php

namespace App\Models\Technician;

use App\Models\Maintenance\Maintenance;
use App\Models\User;
use Database\Factories\TechnicalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Technician extends Model
{

    /** @use HasFactory<TechnicalFactory> */
    use HasFactory;

    protected $table = 'technicians';

    protected $fillable = [
        'user_id',
        'document',
        'phone',
        'address',
        'status',
        'blood_type',
        'photo',
        'specialty',
        'hire_date',
        'contract_type',
        'eps_pdf',
        'arl_pdf',
        'pension_pdf'
    ];

    public static array $rules = [
        'document' => 'string|max:20',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'photo' => 'nullable|string|max:500',
        'specialty' => 'string|max:100',
        'hire_date' => 'date',
        'contract_type' => 'in:full_time,part_time,contractor',
        'eps_pdf' => 'nullable|string|max:500',
        'arl_pdf' => 'nullable|string|max:500',
        'pension_pdf' => 'nullable|string|max:500'
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenances() : hasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    protected static function newFactory(): TechnicalFactory
    {
        return TechnicalFactory::new();
    }

    public function getPhotoAttribute(): ?string
    {
        if ($photo = Arr::get($this->attributes, 'photo'))
            return config('filesystems.disks.s3.url') . 'images/' . $photo . '.PNG';
        return null;
    }

    public function getEpsPdfUrlAttribute(): ?string
    {
        if ($value = Arr::get($this->attributes, 'eps_pdf'))
            return config('filesystems.disks.s3.url') . 'docs/pdfs/' . $value;
        return null;
    }

    public function getArlPdfUrlAttribute(): ?string
    {
        if ($value = Arr::get($this->attributes, 'arl_pdf'))
            return config('filesystems.disks.s3.url') . 'docs/pdfs/' . $value;
        return null;
    }

    public function getPensionPdfUrlAttribute(): ?string
    {
        if ($value = Arr::get($this->attributes, 'pension_pdf'))
            return config('filesystems.disks.s3.url') . 'docs/pdfs/' . $value;
        return null;
    }
}

