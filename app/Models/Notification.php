<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'message',
        'type',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Scope query to only unread notifications.
     */
    public function scopeBelumDibaca(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope query to only read notifications.
     */
    public function scopeSudahDibaca(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Is this notification unread?
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Is this notification already read?
     */
    public function isRead(): bool
    {
        return ! $this->isUnread();
    }
}