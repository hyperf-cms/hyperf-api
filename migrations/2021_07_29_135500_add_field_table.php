<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class AddFieldTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //好友聊天表增加文件后缀
        Schema::table('ct_friend_chat_history', function (Blueprint $table) {
            $table->string('file_ext', 255)->default('')->comment('文件后缀')->after('file_name');
        });
        //好友组增加字段
        Schema::table('ct_friend_group', function (Blueprint $table) {
            $table->integer('sort')->default(99)->comment('分组顺序 按顺序从小到大')->after('friend_group_name');
        });
        //好友关系表增加字段
        Schema::table('ct_friend_relation', function (Blueprint $table) {
            $table->string('friend_remark', 255)->default('')->comment('好友备注')->after('friend_id');
            $table->integer('friend_group')->default(0)->comment('好友分组')->after('friend_remark');
            $table->tinyInteger('is_up')->default(0)->comment('是否置顶1：是 0：否')->after('friend_group');
            $table->tinyInteger('is_not_disturb')->default(0)->comment('是否消息免打扰')->after('is_up');
        });
        //好友关系表增加字段
        Schema::table('ct_group_relation', function (Blueprint $table) {
            $table->tinyInteger('is_up')->default(0)->comment('是否置顶1：是 0：否')->after('group_id');
            $table->tinyInteger('is_not_disturb')->default(0)->comment('是否消息免打扰')->after('is_up');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
}
