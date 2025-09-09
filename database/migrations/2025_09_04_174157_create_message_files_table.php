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
    Schema::create('message_files', function (Blueprint $table) {
        $table->id();
        $table->foreignId('message_id')->constrained()->onDelete('cascade');
        $table->string('file_path');
        $table->string('original_name');
        $table->string('mime_type');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('message_files');
}
};
