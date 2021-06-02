<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCtFriendGroupTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ct_friend_group', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('uid', 11)->default('0')->comment('用户ID');
            $table->string('friend_group_name', 255)->default('0')->comment('分组名');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `ct_friend_group` comment'聊天群组表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ct_friend_group');
    }
}
