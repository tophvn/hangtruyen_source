<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('manga_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained('manga_metadata')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 sao');
            $table->timestamps();
            
            $table->unique(['manga_id', 'user_id'], 'unique_manga_user_rating');
            $table->index('manga_id');
            $table->index('user_id');
            $table->index('rating');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manga_ratings');
    }
}
