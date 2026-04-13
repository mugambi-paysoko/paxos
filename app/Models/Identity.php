<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Identity extends Model
{
    protected $fillable = [
        'user_id',
        'paxos_identity_id',
        'ref_id',
        'verifier_type',
        'identity_type',
        'first_name',
        'last_name',
        'date_of_birth',
        'nationality',
        'cip_id',
        'cip_id_type',
        'cip_id_country',
        'phone_number',
        'email',
        'address_country',
        'address1',
        'city',
        'province',
        'zip_code',
        'institution_details',
        'institution_members',
        'id_verification_status',
        'sanctions_verification_status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'institution_details' => 'array',
        'institution_members' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function fiatAccounts(): HasMany
    {
        return $this->hasMany(FiatAccount::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(IdentityDocument::class);
    }
}
