<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTimedTaskLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timed_task_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('task_id')->comment('任务ID');
            $table->string('task_name', 255)->default('')->comment('任务名');
            $table->string('task', 255)->default('')->comment('任务');
            $table->integer('execute_time')->comment('执行时间');
            $table->text('error_log')->comment('错误信息');
            $table->tinyInteger('result')->default('0')->comment('执行结果 1：成功 0：失败');
            $table->index('task_id', 'task_id_index');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".(env('DB_PREFIX','')?:'')."timed_task_log` comment'任务日志表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timed_task_log');
    }
}
