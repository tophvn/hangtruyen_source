<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateApiHealthTable extends Migration
{
    public function up()
    {
        Schema::create('api_health', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['otruyen'])->unique();
            $table->boolean('is_available')->default(true);
            $table->timestamp('last_check_at')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('retry_after')->nullable();
            $table->timestamps();
        });
        
        DB::table('api_health')->insert([
            ['source_type' => 'otruyen', 'is_available' => true, 'last_check_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('api_health');
    }
}
