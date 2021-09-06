<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateBiliVideoReportTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bili_video_report', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('time')->default('0')->comment('时间戳');
            $table->string('bvid', 255)->default('')->comment('视频ID');
            $table->string('mid', 255)->default('')->comment('用户ID');
            $table->integer('view')->default('0')->comment('视频播放数');
            $table->integer('danmaku')->default('0')->comment('弹幕数');
            $table->integer('reply')->default('0')->comment('评论数');
            $table->integer('favorite')->default('0')->comment('收藏数');
            $table->integer('coin')->default('0')->comment('投硬币枚数');
            $table->integer('likes')->default('0')->comment('点赞数');
            $table->integer('dislike')->default('0')->comment('踩数');
            $table->primary(['time', 'bvid']);
            $table->index('mid', 'mid_index');
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".env('DB_PREFIX','')."bili_video_report` comment'视频报表数据表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bili_video_report');
    }
}
