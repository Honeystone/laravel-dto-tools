<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Foo extends Model
{
    protected $guarded = [];

    public function bar(): HasOne
    {
        return $this->hasOne(Bar::class);
    }

    public function baz(): HasMany
    {
        return $this->hasMany(Bar::class);
    }
}
