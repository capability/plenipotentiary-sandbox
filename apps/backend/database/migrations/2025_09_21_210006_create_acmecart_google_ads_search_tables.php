<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Campaigns
        Schema::create('acmecart_search_campaign', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 255)->default('');
            $table->unsignedBigInteger('resource_id')->default(0); // remote campaign_id
            $table->string('resource_name', 255)->nullable();
            $table->string('budget_resource_name', 255)->nullable();

            $table->string('status', 255)->default('');
            $table->string('start_date', 255)->default(''); // keep as-is (source was varchar)
            $table->string('end_date', 255)->default('');

            $table->float('daily_budget', 8, 4)->default(0.0000);

            $table->text('network_targeting');
            $table->text('languages');
            $table->text('geo_target_type');
            $table->text('geo_targets');
            $table->text('campaign_negative_keyword_criteria');
            $table->text('campaign_negative_website_criteria');

            $table->tinyInteger('is_enabled_separate_content_bids')->unsigned()->default(0);
            $table->tinyInteger('is_enabled_optimized_ad_serving')->unsigned()->default(0);

            $table->integer('active_stock_product_count')->nullable()->default(0);
            $table->decimal('in_stock_product_value', 12, 2)->nullable()->default(0.00);

            $table->index('resource_id', 'idx_campaign_resource_id');
            $table->index('status', 'idx_campaign_status');
        });

        // 2) Adgroups
        Schema::create('acmecart_search_adgroup', function (Blueprint $table) {
            $table->increments('id');

            // Local FK to campaigns (replaces *_pkid)
            $table->unsignedInteger('campaign_id');

            $table->string('name', 255)->default('');
            $table->unsignedBigInteger('resource_id')->default(0); // remote ad_group_id
            $table->string('resource_name', 255)->nullable();

            $table->float('max_cpc')->default(0);
            $table->float('max_cpm')->default(0);
            $table->float('maxContent_cpc')->default(0);

            $table->string('status', 255)->default('');
            $table->string('param1_value', 16)->nullable();
            $table->string('param2_value', 16)->nullable();

            $table->decimal('avg_cpc', 8, 2)->nullable();
            $table->integer('impressions')->nullable();
            $table->integer('clicks')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->decimal('search_impr_share', 8, 2)->nullable();
            $table->decimal('search_lost_is_rank', 8, 2)->nullable();
            $table->decimal('search_abs_top_is', 8, 2)->nullable();
            $table->decimal('ctr', 8, 2)->nullable();
            $table->decimal('conversions', 8, 2)->nullable();
            $table->decimal('first_page_cpc', 8, 2)->nullable();

            $table->integer('product_id')->nullable();
            $table->tinyInteger('is_pmax_zombie')->nullable();
            $table->tinyInteger('is_product_active')->default(0);

            $table->string('price_customizer_link_resource_name', 255)->nullable();
            $table->string('price_customizer_text', 16)->nullable();
            $table->string('stock_customizer_link_resource_name', 255)->nullable();
            $table->string('stock_customizer_text', 16)->nullable();

            $table->string('thumbnail_url', 255)->nullable();
            $table->string('image_asset_link_resource_name', 255)->nullable();

            $table->tinyInteger('needs_sync')->default(0);

            $table->index('campaign_id', 'idx_adgroup_campaign_id');
            $table->index('resource_id', 'idx_adgroup_resource_id');
            $table->index('product_id', 'idx_adgroup_product_id');

            $table->foreign('campaign_id')
                ->references('id')->on('acmecart_search_campaign')
                ->cascadeOnDelete();
        });

        // 3) Ads
        Schema::create('acmecart_search_ad', function (Blueprint $table) {
            $table->increments('id');

            // Local FK to adgroups (replaces adgroup_pkid)
            $table->unsignedInteger('adgroup_id');

            $table->unsignedBigInteger('resource_id')->default(0); // remote ad_id
            $table->string('resource_name', 255)->nullable();

            $table->string('display_url_path1', 15)->default('');
            $table->string('display_url_path2', 15)->nullable();
            $table->string('destination_url', 255)->default('');

            $table->tinyInteger('is_deleted')->default(0);
            $table->tinyInteger('is_disapproved')->default(0);

            $table->string('pinned_headline1', 60)->default('');
            $table->string('pinned_headline2', 60)->nullable();
            $table->string('headline3', 60)->nullable();
            $table->string('headline4', 60)->nullable();
            $table->string('headline5', 60)->nullable();
            $table->string('headline6', 60)->nullable();
            $table->string('headline7', 60)->nullable();
            $table->string('headline8', 60)->nullable();
            $table->string('headline9', 60)->nullable();
            $table->string('headline10', 60)->nullable();
            $table->string('headline11', 60)->nullable();
            $table->string('headline12', 60)->nullable();
            $table->string('headline13', 60)->nullable();
            $table->string('headline14', 60)->nullable();

            $table->string('description1', 90)->default('');
            $table->string('description2', 90)->default('');
            $table->string('description3', 90)->nullable();
            $table->string('description4', 90)->nullable();

            $table->string('status', 32)->nullable();
            $table->string('approval_status', 32)->default('UNCHECKED');
            $table->tinyInteger('is_free_delivery')->default(0);
            $table->text('disapproval_reasons')->nullable();

            $table->integer('product_id')->nullable();

            $table->index('adgroup_id', 'idx_ad_adgroup_id');
            $table->index('resource_id', 'idx_ad_resource_id');
            $table->index('product_id', 'idx_ad_product_id');

            $table->foreign('adgroup_id')
                ->references('id')->on('acmecart_search_adgroup')
                ->cascadeOnDelete();
        });

        // 4) Criteria
        Schema::create('acmecart_search_adgroup_criterion', function (Blueprint $table) {
            $table->increments('id');

            // Local FKs to adgroups (replaces adgroup_pkid / priority_adgroup_pkid)
            $table->unsignedInteger('adgroup_id')->nullable();
            $table->unsignedInteger('priority_adgroup_id')->nullable();

            $table->string('text', 255)->default('');
            $table->unsignedBigInteger('resource_id')->default(0); // remote criterion_id
            $table->string('resource_name', 255)->nullable();

            $table->string('type', 255)->default('');
            $table->string('criterion_type', 255)->default('');
            $table->tinyInteger('is_negative')->default(0);

            $table->float('max_cpc', 8, 4)->default(0.0000);
            $table->float('min_cpc', 8, 4)->default(0.0000);

            $table->string('status', 255)->default('');
            $table->string('language', 255)->default('');
            $table->string('destination_url', 255)->default('');

            $table->index('adgroup_id', 'idx_crit_adgroup_id');
            $table->index('resource_id', 'idx_crit_resource_id');

            // Prefix index like original: KEY `text` (`text`(25))
            // Laravel schema builder doesn't expose prefix length, so we use raw SQL after the table exists.
        });

        // Add prefix index for `text` column
        DB::statement('CREATE INDEX idx_crit_text_25 ON acmecart_search_adgroup_criterion (`text`(25));');

        // Foreign keys for criteria (after table exists so we can also add SET NULL rule)
        Schema::table('acmecart_search_adgroup_criterion', function (Blueprint $table) {
            $table->foreign('adgroup_id')
                ->references('id')->on('acmecart_search_adgroup')
                ->cascadeOnDelete();

            $table->foreign('priority_adgroup_id')
                ->references('id')->on('acmecart_search_adgroup')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Drop in reverse FK dependency order
        Schema::table('acmecart_search_adgroup_criterion', function (Blueprint $table) {
            // Guarded drops (FKs may or may not exist depending on partial runs)
            try { $table->dropForeign(['adgroup_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['priority_adgroup_id']); } catch (\Throwable $e) {}
        });

        Schema::table('acmecart_search_ad', function (Blueprint $table) {
            try { $table->dropForeign(['adgroup_id']); } catch (\Throwable $e) {}
        });

        Schema::table('acmecart_search_adgroup', function (Blueprint $table) {
            try { $table->dropForeign(['campaign_id']); } catch (\Throwable $e) {}
        });

        // Drop prefix index via raw SQL before dropping table
        try { DB::statement('DROP INDEX idx_crit_text_25 ON acmecart_search_adgroup_criterion'); } catch (\Throwable $e) {}

        Schema::dropIfExists('acmecart_search_adgroup_criterion');
        Schema::dropIfExists('acmecart_search_ad');
        Schema::dropIfExists('acmecart_search_adgroup');
        Schema::dropIfExists('acmecart_search_campaign');
    }
};

