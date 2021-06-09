<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateOperateLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operate_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('action', '255')->default('')->comment('操作');
            $table->text('data')->comment('请求参数');
            $table->string('username', '100')->default('')->comment('操作人账号');
            $table->string('operator', '100')->default('')->comment('操作人描述');
            $table->string('response_result', '1000')->default('')->comment('响应结果');
            $table->string('response_code', '50')->default('')->comment('响应状态码');
            $table->string('target_class', '50')->default('')->comment('目标类');
            $table->string('target_method', '50')->default('')->comment('目标方法');
            $table->string('request_ip', '50')->default('')->comment('请求IP');
            $table->string('request_method', '50')->default('')->comment('请求方法');
            $table->string('target_url', '100')->default('')->comment('目标路由');
            $table->integer('uid')->default(0)->comment('操作人ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operate_log');
    }
}
