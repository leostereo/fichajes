<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usuarios extends Model
{
        protected $fillable = [

        'tele_id', 'chat_id', 'first_name', 'last_name', 'privileg', 'day', 'category', 'score', 'diff'

        ];


}
