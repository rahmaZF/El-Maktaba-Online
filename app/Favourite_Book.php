<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favourite_Book extends Model
{
    public function user () {
        return $this->belongsTo('App\User');
    }

    public function book () {
        return $this->belongsTo('App\Book');
    }
}
