<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'label_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }

}
