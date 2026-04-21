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

    public function fiatWithdrawals()
    {
        return $this->hasMany(FiatWithdrawal::class);
    }

    public function cryptoWithdrawals()
    {
        return $this->hasMany(CryptoWithdrawal::class);
    }

    public function depositAddresses()
    {
        return $this->hasMany(DepositAddress::class);
    }

    public function isLender(): bool
    {
        return $this->isInstitution();
    }

    public function isBorrower(): bool
    {
        return $this->isIndividual();
    }

    public function isInstitution(): bool
    {
        return in_array($this->role, ['institution', 'lender'], true);
    }

    public function isIndividual(): bool
    {
        return in_array($this->role, ['individual', 'borrower'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'institution', 'lender' => 'Institution',
            'individual', 'borrower' => 'Individual',
            'admin' => 'Admin',
            default => ucfirst((string) $this->role),
        };
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
