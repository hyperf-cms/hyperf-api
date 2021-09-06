<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateBiliUpUserTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bili_up_user', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('mid', 255)->default('')->comment('用户ID')->primary();
            $table->string('name', 255)->default('')->comment('名称');
            $table->string('sex', 50)->default('未知')->comment('性别');
            $table->string('sign', 500)->default('未知')->comment('签名');
            $table->string('face', 255)->default('未知')->comment('头像');
            $table->tinyInteger('level')->default('0')->comment('等级');
            $table->string('top_photo', 255)->default('')->comment('头图');
            $table->string('live_room_info', 1000)->default('')->comment('直播间信息');
            $table->string('birthday', 100)->default('')->comment('生日');
            $table->integer('following')->default('0')->comment('关注数');
            $table->integer('follower')->default('0')->comment('粉丝数');
            $table->integer('video_play')->default('0')->comment('视频播放数');
            $table->integer('readling')->default('0')->comment('阅读数');
            $table->integer('likes')->default('0')->comment('获赞数');
            $table->integer('recharge_month')->default('0')->comment('月充电数');
            $table->integer('recharge_total')->default('0')->comment('总充电数');
            $table->tinyInteger('timed_status')->default('0')->comment('定时任务状态');
            $table->index('name', 'name_index');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".env('DB_PREFIX','')."bili_up_user` comment'Up主信息表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bili_up_user');
    }
}
