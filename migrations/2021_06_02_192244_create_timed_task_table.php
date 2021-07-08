<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateTimedTaskTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timed_task', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 255)->default('')->comment('定时任务名');
            $table->string('params', 500)->default('')->comment('作为参数');
            $table->string('task', 255)->default('')->comment('task任务名');
            $table->string('execute_time', 255)->default('')->comment('执行时间');
            $table->string('next_execute_time', 255)->default('')->comment('下次执行时间');
            $table->string('desc', 255)->default('')->comment('备注信息');
            $table->integer('times')->default('0')->comment('执行次数');
            $table->tinyInteger('status')->default('0')->comment('1:启用 0：禁用');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".(env('DB_PREFIX','')?:'')."timed_task` comment'定时任务表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timed_task');
    }
}
