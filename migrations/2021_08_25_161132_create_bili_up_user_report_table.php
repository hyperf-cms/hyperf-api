<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateBiliUpUserReportTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bili_up_user_report', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('time')->default('0')->comment('时间戳');
            $table->string('mid', 255)->default('')->comment('用户ID');
            $table->integer('following')->default('0')->comment('关注数');
            $table->integer('follower')->default('0')->comment('粉丝数');
            $table->integer('video_play')->default('0')->comment('视频播放数');
            $table->integer('readling')->default('0')->comment('阅读数');
            $table->integer('likes')->default('0')->comment('获赞数');
            $table->integer('recharge_month')->default('0')->comment('月充电数');
            $table->integer('recharge_total')->default('0')->comment('总充电数');
            $table->primary(['time', 'mid']);
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `bili_up_user_report` comment'Up主信息数据报表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bili_up_user_report');
    }
}
