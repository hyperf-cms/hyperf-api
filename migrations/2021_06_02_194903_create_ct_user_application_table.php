<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCtUserApplicationTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ct_user_application', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id')->default('0');
            $table->integer('uid', 11)->default('0')->comment('用户ID');
            $table->integer('receiver_id', 11)->default('0')->comment('接收方');
            $table->string('group_id', 50)->default('')->comment('好友分组ID || 群');
            $table->tinyInteger('application_type', 3)->default('1')->comment('申请类型 1好友 2群');
            $table->tinyInteger('application_status', 3)->default('0')->comment('申请状态 0创建 1同意 2拒绝');
            $table->string('application_reason', 255)->default('0')->comment('申请原因');
            $table->tinyInteger('read_state', 3)->default('0')->comment('读取状态 0 未读 1已读');
            $table->timestamps();
            $table->index('uid', 'uid_index');
            $table->index('receiver_id', 'receiver_id_index');
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `ct_user_application` comment'好友/群组申请表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ct_user_application');
    }
}
