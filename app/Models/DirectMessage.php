<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    /** @use HasFactory<\Database\Factories\DirectMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'message_text',
        'channel',
    ];

    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'direct_message_recipients', 'direct_message_id', 'recipient_id')
            ->withPivot('delivered', 'delivered_at')
            ->withTimestamps();
    }
}
