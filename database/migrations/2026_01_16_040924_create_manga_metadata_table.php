<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manga_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('Slug của website');
            $table->enum('source_type', ['otruyen'])->comment('Nguồn API');
            $table->string('source_identifier')->comment('ID hoặc slug từ API gốc');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_url')->comment('URL ảnh cover');
            $table->string('panorama_url')->nullable()->comment('URL ảnh panorama');
            $table->string('author')->nullable();
            $table->enum('status', ['ongoing', 'completed', 'hiatus'])->nullable();
            $table->json('tags')->nullable()->comment('Array tags');
            $table->integer('views_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable()->comment('Rating 0-5');
            $table->integer('chapters_count')->default(0)->comment('Số chapter (cached)');
            $table->string('last_chapter_number')->nullable()->comment('Chapter mới nhất để detect update');
            $table->timestamp('last_synced_at')->nullable()->comment('Lần cuối sync metadata');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('slug');
            $table->index(['source_type', 'source_identifier']);
            $table->index('last_synced_at');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manga_metadata');
    }
}
