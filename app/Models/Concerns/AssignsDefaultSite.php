<?php

namespace App\Models\Concerns;

use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

trait AssignsDefaultSite
{
    protected static function bootAssignsDefaultSite(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('site_id') !== null) {
                return;
            }

            $siteId = Site::query()->where('nom', 'Cotonou')->value('id');

            if ($siteId === null) {
                throw new RuntimeException('The default Cotonou site has not been seeded.');
            }

            $model->setAttribute('site_id', $siteId);
        });
    }
}
