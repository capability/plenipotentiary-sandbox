<?php

namespace App\Models\AcmeCart\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdGroupCriterion extends Model
{
    protected $table = 'acmecart_search_adgroup_criterion';
    public $timestamps = false;

    protected $fillable = [
        'adgroup_id',
        'priority_adgroup_id',
        'text',
        'resource_id',
        'resource_name',
        'type',
        'criterion_type',
        'is_negative',
        'max_cpc',
        'min_cpc',
        'status',
        'language',
        'destination_url',
    ];

    protected $casts = [
        'adgroup_id' => 'int',
        'priority_adgroup_id' => 'int',
        'resource_id' => 'int',
        'is_negative' => 'bool',
        'max_cpc' => 'float',
        'min_cpc' => 'float',
    ];

    public function adgroup(): BelongsTo
    {
        return $this->belongsTo(AdGroup::class, 'adgroup_id');
    }

    public function priorityAdgroup(): BelongsTo
    {
        return $this->belongsTo(AdGroup::class, 'priority_adgroup_id');
    }
}
