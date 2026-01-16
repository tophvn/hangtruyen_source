<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaDailyViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manga_daily_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained('manga_metadata')->onDelete('cascade');
            $table->date('view_date')->comment('Ngày của views');
            $table->unsignedInteger('views_count')->default(0)->comment('Số lượt xem trong ngày');
            $table->timestamps();
            
            $table->unique(['manga_id', 'view_date'], 'unique_manga_daily_view');
            $table->index('manga_id');
            $table->index('view_date');
            $table->index('views_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manga_daily_views');
    }
}
