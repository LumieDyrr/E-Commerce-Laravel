<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Slug produk harus unik: rapikan duplikat lama dulu.
        $dupes = DB::table('products')->select('slug')->groupBy('slug')->havingRaw('count(*) > 1')->pluck('slug');
        foreach ($dupes as $slug) {
            foreach (DB::table('products')->where('slug', $slug)->orderBy('id')->get()->skip(1) as $row) {
                DB::table('products')->where('id', $row->id)->update(['slug' => $slug . '-' . $row->id]);
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->softDeletes();
            $table->unique('slug');
            $table->index('category_id');
        });

        // 2. Jenis paket langganan (basic/premium/eksklusif) tidak cocok dengan enum lama.
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('subscriptions_type', 20)->change();
            $table->index(['user_id', 'status']);
        });

        // Bug lama: start_date == end_date. Pulihkan start_date = end_date - 30 hari.
        foreach (DB::table('subscriptions')->whereColumn('start_date', 'end_date')->get() as $row) {
            DB::table('subscriptions')->where('id', $row->id)
                ->update(['start_date' => Carbon::parse($row->end_date)->subDays(30)->toDateString()]);
        }

        // 3. Pesanan: bedakan pesanan produk vs langganan secara eksplisit.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type', 20)->default('product')->after('user_id');
            $table->string('subscription_plan', 20)->nullable()->after('type');
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
        });

        DB::table('orders')->whereNull('shipping_address')->update(['type' => 'subscription']);
        foreach (['basic' => 199000, 'premium' => 299000, 'eksklusif' => 499000] as $plan => $price) {
            DB::table('orders')->where('type', 'subscription')->where('total_price', $price)
                ->update(['subscription_plan' => $plan]);
        }

        // 4. Indeks pendukung
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index('order_id');
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->index(['user_id', 'product_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('comment')->nullable()->change();
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });
        Schema::table('carts', fn (Blueprint $t) => $t->dropIndex(['user_id', 'product_id']));
        Schema::table('payments', fn (Blueprint $t) => $t->dropIndex(['order_id']));
        Schema::table('order_items', function (Blueprint $t) {
            $t->dropIndex(['order_id']);
            $t->dropIndex(['product_id']);
        });
        Schema::table('orders', function (Blueprint $t) {
            $t->dropIndex(['user_id', 'status']);
            $t->dropIndex(['type', 'status']);
            $t->dropColumn(['type', 'subscription_plan']);
        });
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropIndex(['user_id', 'status']));
        Schema::table('products', function (Blueprint $t) {
            $t->dropUnique(['slug']);
            $t->dropIndex(['category_id']);
            $t->dropSoftDeletes();
        });
    }
};
