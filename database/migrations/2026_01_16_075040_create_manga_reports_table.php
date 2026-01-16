<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMangaReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manga_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained('manga_metadata')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('chapter_slug')->nullable()->comment('Chapter slug nếu báo lỗi về chapter cụ thể');
            $table->text('content');
            $table->enum('status', ['pending', 'resolved', 'rejected'])->default('pending');
            $table->timestamps();
            
            $table->index('manga_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manga_reports');
    }
}
