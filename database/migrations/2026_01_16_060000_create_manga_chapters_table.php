<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaChaptersTable extends Migration
{
    public function up()
    {
        Schema::create('manga_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained('manga_metadata')->onDelete('cascade');
            $table->string('chapter_slug', 100)->comment('chapter-1, chapter-2, etc');
            $table->string('chapter_name', 100)->comment('Chapter name từ API');
            $table->string('chapter_api_id')->nullable()->comment('ID từ API nếu có');
            $table->unsignedInteger('views_count')->default(0)->comment('Số lượt xem');
            $table->timestamp('first_viewed_at')->nullable()->comment('Lần đầu có view');
            $table->timestamp('last_viewed_at')->nullable()->comment('Lần cuối có view');
            $table->timestamps();
            
            $table->unique(['manga_id', 'chapter_slug'], 'unique_manga_chapter');
            $table->index('manga_id');
            $table->index('views_count');
            $table->index('last_viewed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manga_chapters');
    }
}
