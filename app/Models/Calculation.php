<?php

namespace App\Models;

use App\Models\Alternative;
use App\Models\Criteria;
use App\Models\Result;
use App\Models\Score;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calculation extends Model
{
    protected $fillable = ['name', 'user_id'];

    protected static function booted(): void
    {
        static::creating(function (self $calculation): void {
            if (blank($calculation->user_id)) {
                $calculation->user_id = auth()->id();
            }
        });
    }

    public function criteria()
    {
        return $this->hasMany(Criteria::class);
    }

    public function alternatives()
    {
        return $this->hasMany(Alternative::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
