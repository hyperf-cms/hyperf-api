<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCtFriendRelationTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ct_friend_relation', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('uid')->default('0')->comment('用户ID');
            $table->integer('friend_id')->default('0')->comment('好友ID');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `ct_friend_relation` comment'好友关系表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ct_friend_relation');
    }
}
