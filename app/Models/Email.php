<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'email_id',
        'reciver_id',
        'sender_id',
        'subject',
        'body',

    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    public function reciver()
    {
        return $this->belongsTo(User::class,'reciver_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function cc()
    {
        return $this->hasMany(Email_cc::class,'cc_id');
    }

    public function bcc()
    {
        return $this->hasMany(Email_bcc::class, 'bcc_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class,'');
    }
}
