<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email_bcc extends Model
{
    use HasFactory;
    protected $fillable = [
        'email_id',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function email()
    {
        return $this->belongsTo(Email::class,'email_id');
    }
}
