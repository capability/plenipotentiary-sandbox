<?php

namespace App\Models\AcmeCart\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdGroup extends Model
{
    protected $table = 'acmecart_search_adgroup';
    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'name',
        'resource_id',
        'resource_name',
        'max_cpc',
        'max_cpm',
        'maxContent_cpc',
        'status',
        'param1_value',
        'param2_value',
        'avg_cpc',
        'impressions',
        'clicks',
        'cost',
        'search_impr_share',
        'search_lost_is_rank',
        'search_abs_top_is',
        'ctr',
        'conversions',
        'first_page_cpc',
        'product_id',
        'is_pmax_zombie',
        'is_product_active',
        'price_customizer_link_resource_name',
        'price_customizer_text',
        'stock_customizer_link_resource_name',
        'stock_customizer_text',
        'thumbnail_url',
        'image_asset_link_resource_name',
        'needs_sync',
    ];

    protected $casts = [
        'campaign_id' => 'int',
        'resource_id' => 'int',
        'max_cpc' => 'float',
        'max_cpm' => 'float',
        'maxContent_cpc' => 'float',
        'avg_cpc' => 'decimal:2',
        'impressions' => 'int',
        'clicks' => 'int',
        'cost' => 'decimal:2',
        'search_impr_share' => 'decimal:2',
        'search_lost_is_rank' => 'decimal:2',
        'search_abs_top_is' => 'decimal:2',
        'ctr' => 'decimal:2',
        'conversions' => 'decimal:2',
        'first_page_cpc' => 'decimal:2',
        'product_id' => 'int',
        'is_pmax_zombie' => 'int',
        'is_product_active' => 'bool',
        'needs_sync' => 'bool',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class, 'adgroup_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(AdGroupCriterion::class, 'adgroup_id');
    }
}
