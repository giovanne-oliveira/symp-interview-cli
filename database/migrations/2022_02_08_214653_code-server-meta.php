<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CodeServerMeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_server_instances', function(Blueprint $table){
            $table->id();
            $table->string('candidate_name');
            $table->integer('pid');
            $table->string('password');
            $table->integer('status');
            $table->datetime('started_at')->nullable();
            $table->datetime('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('code_server_instances');
    }
}
