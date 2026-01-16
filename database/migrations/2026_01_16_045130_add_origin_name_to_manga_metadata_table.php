<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginNameToMangaMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manga_metadata', function (Blueprint $table) {
            $table->json('origin_name')->nullable()->after('title')->comment('Tên gốc/tên phụ của truyện (array)');
        });
    }

    public function down()
    {
        Schema::table('manga_metadata', function (Blueprint $table) {
            $table->dropColumn('origin_name');
        });
    }
}
