<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateOperateLog extends Migration
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
            $table->text('params')->default('')->comment('请求参数');
            $table->string('username', '100')->default('')->comment('操作人账号');
            $table->string('operator', '100')->default('')->comment('操作人描述');
            $table->string('deal_result', '255')->default('')->comment('处理结果');
            $table->string('deal_exception', '1000')->default('')->comment('处理异常');
            $table->tinyinteger('status')->default(1)->comment('账号状态 0-停用，1-启用');
            $table->integer('uid')->default(0)->comment('操作人ID');
            $table->string('ip', 50)->default('')->comment('操作地点IP');
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
