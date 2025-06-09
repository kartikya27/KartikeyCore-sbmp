<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kartikey\Core\Interface\Address as AddressContract;
use Stegback\User\Models\User;

abstract class Address extends Model implements AddressContract
{
    /**
     * Table.
     *
     * @var string
     */
    protected $table = USER_ADDRESS;

    /**
     * Guarded.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Castable.
     *
     * @var array
     */
    protected $casts = [
        'use_for_shipping' => 'boolean',
        'default_address'  => 'boolean',
    ];

    /**
     * Get all the attributes for the attribute groups.
     */
    // public function getNameAttribute(): string
    // {
    //     return ($this->first_name ?? $this->name) . ' ' . $this->last_name;
    // }

    /**
     * Get the customer record associated with the address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
