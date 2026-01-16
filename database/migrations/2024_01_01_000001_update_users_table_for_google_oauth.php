<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableForGoogleOauth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Kiểm tra và thêm các cột mới
        if (!Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('google_id')->nullable()->unique()->after('email');
            });
        }
        
        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('google_id');
            });
        }
        
        if (!Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('updated_at');
            });
        }

        // Thay đổi password thành nullable (cần dùng DB facade)
        if (Schema::hasColumn('users', 'password')) {
            \DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar', 'last_login_at']);
            $table->string('password')->nullable(false)->change();
        });
    }
}
