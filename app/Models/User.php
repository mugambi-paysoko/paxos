<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function identities()
    {
        return $this->hasMany(Identity::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function fiatAccounts()
    {
        return $this->hasMany(FiatAccount::class);
    }

    public function fiatDepositInstructions()
    {
        return $this->hasMany(FiatDepositInstruction::class);
    }

    public function fiatDeposits()
    {
        return $this->hasMany(FiatDeposit::class);
    }

    public function isLender(): bool
    {
        return $this->role === 'lender';
    }

    public function isBorrower(): bool
    {
        return $this->role === 'borrower';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has a Paxos identity
     */
    public function hasPaxosIdentity(): bool
    {
        return $this->identities()->whereNotNull('paxos_identity_id')->exists();
    }

    /**
     * Get the user's primary Paxos identity (first approved one, or first one)
     */
    public function getPrimaryIdentity(): ?Identity
    {
        return $this->identities()
            ->whereNotNull('paxos_identity_id')
            ->where('id_verification_status', 'APPROVED')
            ->where('sanctions_verification_status', 'APPROVED')
            ->first() 
            ?? $this->identities()->whereNotNull('paxos_identity_id')->first();
    }
}
