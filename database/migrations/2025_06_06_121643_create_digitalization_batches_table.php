<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('digitalization_batches', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('folder_path')->nullable();
            $table->timestamps();
        });

        Schema::table('digitalizations', function (Blueprint $table) {
            $table->foreignId('digitalization_batch_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digitalizations', function (Blueprint $table) {
            $table->dropForeign(['digitalization_batch_id']);
            $table->dropColumn('digitalization_batch_id');
        });


        Schema::dropIfExists('digitalization_batches');
    }
};
