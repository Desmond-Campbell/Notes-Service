<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('content_full')->nullable();
            $table->integer('pages')->nullable();
            $table->string('tags')->nullable();
            $table->string('keywords')->nullable();
            $table->string('folder_id');
            $table->text('permissions')->nullable();
            $table->string('access_level')->nullable();
            $table->boolean('is_private')->nullable()->default(0);
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
