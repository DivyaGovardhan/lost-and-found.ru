<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_ID')->constrained('users')->onDelete('cascade');
            $table->string('title', 100);
            $table->text('description');
            $table->foreignId('category_ID')->constrained('categories')->onDelete('restrict');
            $table->dateTime('date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('photo', 46)->nullable();
            $table->foreignId('found_ID')->constrained('found')->onDelete('restrict');
            $table->foreignId('status_ID')->constrained('statuses')->default(1)->onDelete('restrict');
            $table->unsignedTinyInteger('complaint_number')->default(0);
            $table->string('street', 100)->nullable();
            $table->string('house', 20)->nullable();
            $table->foreignId('district_ID')->constrained('districts')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};