<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCtGroupChatHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ct_group_chat_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('message_id', 50)->default('')->comment('消息ID');
            $table->integer('from_uid', 11)->default('0')->comment('接收好友前');
            $table->string('to_group_id', 50)->default('0')->comment('接收群');
            $table->string('type', 50)->default('1')->comment('消息类型 1：text, 2: image, 3:file, 4:event');
            $table->string('status', 50)->default('')->comment('消息发送状态 going,failed,succeed');
            $table->bigInteger('send_time')->default('0')->comment('发送时间 13位毫秒');
            $table->text('content')->comment('消息内容');
            $table->integer('file_size', 11)->default('0')->comment('文件大小');
            $table->string('file_name', 255)->default('')->comment('文件名称');
            $table->tinyInteger('reception_state', 3)->default('0')->comment('接受状态 0 未接收 1：接收');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `ct_group_chat_history` comment'群组聊天记录'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ct_group_chat_history');
    }
}
