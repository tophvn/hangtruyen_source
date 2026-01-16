<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manga_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained('manga_metadata')->onDelete('cascade');
            $table->foreignId('chapter_id')->nullable()->constrained('manga_chapters')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('manga_comments')->onDelete('cascade');
            $table->text('content');
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();
            
            $table->index('manga_id');
            $table->index('chapter_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manga_comments');
    }
}
