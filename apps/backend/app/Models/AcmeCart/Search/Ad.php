<?php

namespace App\Models\AcmeCart\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    protected $table = 'acmecart_search_ad';
    public $timestamps = false;

    protected $fillable = [
        'adgroup_id',
        'resource_id',
        'resource_name',
        'display_url_path1',
        'display_url_path2',
        'destination_url',
        'is_deleted',
        'is_disapproved',
        'pinned_headline1',
        'pinned_headline2',
        'headline3',
        'headline4',
        'headline5',
        'headline6',
        'headline7',
        'headline8',
        'headline9',
        'headline10',
        'headline11',
        'headline12',
        'headline13',
        'headline14',
        'description1',
        'description2',
        'description3',
        'description4',
        'status',
        'approval_status',
        'is_free_delivery',
        'disapproval_reasons',
        'product_id',
    ];

    protected $casts = [
        'adgroup_id' => 'int',
        'resource_id' => 'int',
        'is_deleted' => 'bool',
        'is_disapproved' => 'bool',
        'is_free_delivery' => 'bool',
        'product_id' => 'int',
    ];

    public function adgroup(): BelongsTo
    {
        return $this->belongsTo(AdGroup::class, 'adgroup_id');
    }
}
