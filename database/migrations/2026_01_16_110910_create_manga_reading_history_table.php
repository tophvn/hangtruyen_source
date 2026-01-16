<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaReadingHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manga_reading_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('manga_id')->constrained('manga_metadata')->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained('manga_chapters')->onDelete('cascade');
            $table->string('chapter_slug')->nullable()->comment('Chapter slug để dễ truy vấn');
            $table->timestamp('last_read_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['user_id', 'manga_id'], 'unique_user_manga_reading');
            $table->index('user_id');
            $table->index('manga_id');
            $table->index('last_read_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manga_reading_history');
    }
}
