<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->json('file_name')->nullable()->change();
            $table->json('file_original_name')->nullable()->change();
            $table->json('folder_path')->nullable()->change();
            $table->json('file_type')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Change back to string (or whatever original type)
            $table->string('file_name')->nullable()->change();
            $table->string('file_original_name')->nullable()->change();
            $table->string('folder_path')->nullable()->change();
            $table->string('file_type')->nullable()->change();
        });
    }
};
