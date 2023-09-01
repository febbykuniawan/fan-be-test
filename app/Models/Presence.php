<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Presence extends Model
{
    use HasFactory;

    protected $table = 'presence';
    protected $fillable = ['userId', 'type', 'is_approve', 'waktu'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function outWaktu()
    {
        return $this->hasOne(Presence::class, 'userId')->where('type', 'OUT');
    }
}
