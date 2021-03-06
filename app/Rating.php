<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Book;

class Rating extends Model
{
    public function user () {
        return $this->belongsTo('App\User');
    }

    public function book () {
        return $this->belongsTo('App\Book');
    }

}
