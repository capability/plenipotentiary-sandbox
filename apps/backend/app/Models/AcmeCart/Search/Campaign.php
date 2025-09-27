<?php

namespace App\Models\AcmeCart\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $table = 'acmecart_search_campaign';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'resource_id',
        'resource_name',
        'budget_resource_name',
        'status',
        'start_date',
        'end_date',
        'daily_budget',
        'network_targeting',
        'languages',
        'geo_target_type',
        'geo_targets',
        'campaign_negative_keyword_criteria',
        'campaign_negative_website_criteria',
        'is_enabled_separate_content_bids',
        'is_enabled_optimized_ad_serving',
        'active_stock_product_count',
        'in_stock_product_value',
    ];

    protected $casts = [
        'resource_id' => 'int',
        'daily_budget' => 'float',
        'is_enabled_separate_content_bids' => 'bool',
        'is_enabled_optimized_ad_serving' => 'bool',
        'active_stock_product_count' => 'int',
        'in_stock_product_value' => 'decimal:2',
    ];

    public function adgroups(): HasMany
    {
        return $this->hasMany(AdGroup::class, 'campaign_id');
    }
}
