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
        Schema::create('uploaded_photos', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable();
            $table->string('mime_type', 100)->default('image/jpeg');
            $table->unsignedInteger('file_size')->default(0);
            $table->longText('image_data');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_photos');
    }
};
