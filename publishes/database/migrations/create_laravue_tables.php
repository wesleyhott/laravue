<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLaravueTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file');
            $table->string('class');
            $table->string('method');
            $table->string('type');
            $table->string('result')->default('SUCCESS'); // 'success', 'neutral', 'failure'
            $table->string('origin', 200)->nullable();
            $table->ipAddress('ip')->default('127.0.0.1');
            $table->string('user_agent', 200)->nullable();
            $table->string('session', 100)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('line')->nullable();
            $table->text('message');
            $table->timestamps();
            $table->string('usuario_ult_alteracao', 40);
        });

        Schema::create('task_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->String('usuario_ult_alteracao', 40);
            $table->timestamps();
        });

        Schema::create('task_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->String('usuario_ult_alteracao', 40);
            $table->timestamps();
        });

        Schema::create('project_modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->String('usuario_ult_alteracao', 40);
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_group_id')
                ->unsigned()
                ->nullable();
            $table->foreign('task_group_id')
                ->references('id')
                ->on('task_groups')
                ->onDelete('set null');
            $table->integer('task_status_id')
                ->unsigned()
                ->nullable();
            $table->foreign('task_status_id')
                ->references('id')
                ->on('task_statuses')
                ->onDelete('set null');
            $table->integer('user_id')
                ->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->integer('project_module_id')
                ->unsigned();
            $table->foreign('project_module_id')
                ->references('id')
                ->on('project_modules')
                ->onDelete('cascade');
            $table->string('name');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->boolean('is_milestone');
            $table->boolean('is_roadmap');
            $table->String('usuario_ult_alteracao', 40);
            $table->timestamps();
        });

        Schema::create('versions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id')
                ->unsigned()
                ->nullable();
            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('set null');
            $table->integer('version_number');
            $table->integer('feature_number');
            $table->integer('issue_number');
            $table->boolean('is_milestone');
            $table->String('usuario_ult_alteracao', 40);
            $table->timestamps();
        });

        Schema::create('vw_funcionario_mps', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->binary('foto');
            $table->char('mamp', 6);
			$table->string('nome', 60);
			$table->string('email', 50);
			$table->integer('unidade_id');
			$table->string('nome_unidade', 250);
			$table->string('endereco', 614);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vw_funcionario_mps');
        Schema::dropIfExists('versions');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('project_modules');
        Schema::dropIfExists('task_statuses');
        Schema::dropIfExists('task_groups');
        Schema::dropIfExists('monitors');
    }
}
