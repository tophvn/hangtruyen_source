<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTablesRemoveCuutruyen extends Migration
{
    public function up()
    {
        if (Schema::hasTable('manga_metadata')) {
            DB::statement("ALTER TABLE `manga_metadata` MODIFY `source_type` ENUM('otruyen') NOT NULL COMMENT 'Nguồn API'");
        }
        
        if (Schema::hasTable('api_health')) {
            DB::statement("ALTER TABLE `api_health` MODIFY `source_type` ENUM('otruyen') NOT NULL");
            DB::table('api_health')->where('source_type', 'cuutruyen')->delete();
        }
    }

    public function down()
    {
        if (Schema::hasTable('manga_metadata')) {
            DB::statement("ALTER TABLE `manga_metadata` MODIFY `source_type` ENUM('cuutruyen','otruyen') NOT NULL COMMENT 'Nguồn API'");
        }
        
        if (Schema::hasTable('api_health')) {
            DB::statement("ALTER TABLE `api_health` MODIFY `source_type` ENUM('cuutruyen','otruyen') NOT NULL");
        }
    }
}
