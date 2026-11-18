<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TOP SUN (food) or UNILEVER (household), set per main group.
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('division', 20)->nullable()->after('parent_id')->index();
        });

        // The ERP's "ItemGroupDesc" (e.g. "TOP SUN KETCHUP"): the product line
        // merchandisers browse by.
        Schema::table('products', function (Blueprint $table) {
            $table->string('line')->nullable()->after('brand_id')->index();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('merchandising_divisions')->nullable();
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // The ERP keeps a separate customer account per division; a
            // chain may use one account for both.
            $table->string('food_account_code', 20)->nullable()->unique();
            $table->string('cleaning_account_code', 20)->nullable()->unique();
            $table->string('tax_code', 20)->nullable()->index();
            $table->string('type', 30)->nullable()->index();
            $table->string('city')->nullable();
            $table->string('region')->nullable()->index();
            $table->string('address', 500)->nullable();
            $table->string('phone')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_vip')->default(false);
            $table->string('status', 20)->default('active')->index();
            // False for a store a merchandiser typed in because it wasn't listed.
            $table->boolean('is_verified')->default(true);
            $table->unsignedBigInteger('legacy_account_id')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('merchandising_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('submission_key')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnUpdate()->nullOnDelete();
            $table->dateTime('visited_at')->index();
            $table->json('divisions')->nullable();
            $table->json('actions')->nullable();
            $table->json('store_feedback')->nullable();
            $table->boolean('needs_order')->default(false);
            $table->string('note', 200)->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('folders')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('merchandising_visit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('merchandising_visits')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnUpdate()->nullOnDelete();
            // Typed by the merchandiser when the product isn't in the catalog.
            $table->string('product_name')->nullable();
            $table->string('division', 20)->nullable()->index();
            $table->string('stock_status', 20)->nullable()->index();
            $table->string('shelf_position', 20)->nullable();
            $table->string('price_tag', 20)->nullable();
            $table->json('issues')->nullable();
            $table->boolean('needs_order')->default(false);
            $table->timestamps();
        });

        // Chunked uploads land here first; they're attached to a visit (and
        // copied into the file manager) only when the visit is submitted.
        Schema::create('merchandising_uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('merchandising_visits')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('file_entry_id')->nullable()->constrained('file_entries')->cascadeOnUpdate()->nullOnDelete();
            $table->string('kind', 10);
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedBigInteger('received_bytes')->default(0);
            $table->string('temp_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchandising_uploads');
        Schema::dropIfExists('merchandising_visit_items');
        Schema::dropIfExists('merchandising_visits');
        Schema::dropIfExists('stores');

        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('merchandising_divisions'));
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['line']);
            $table->dropColumn('line');
        });
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex(['division']);
            $table->dropColumn('division');
        });
    }
};
