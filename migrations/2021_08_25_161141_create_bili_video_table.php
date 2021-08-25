<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateBiliVideoTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bili_video', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('bvid', 255)->default('')->comment('视频ID')->primary();
            $table->string('mid', 255)->default('')->comment('用户ID');
            $table->string('owner', 1000)->default('')->comment('作者信息');
            $table->string('cover', 255)->default('')->comment('视频封面图');
            $table->string('title', 255)->default('')->comment('标题');
            $table->integer('public_time')->default('0')->comment('视频发布时间');
            $table->string('desc', 500)->default('')->comment('视频描述');
            $table->integer('duration')->default('0')->comment('视频时长(s)');
            $table->integer('view')->default('0')->comment('视频播放数');
            $table->integer('danmaku')->default('0')->comment('弹幕数');
            $table->integer('reply')->default('0')->comment('评论数');
            $table->integer('favorite')->default('0')->comment('收藏数');
            $table->integer('coin')->default('0')->comment('投硬币枚数');
            $table->integer('likes')->default('0')->comment('点赞数');
            $table->integer('dislike')->default('0')->comment('踩数');
            $table->tinyInteger('timed_status')->default('0')->comment('定时任务状态');
            $table->index('mid', 'mid_index');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `bili_video` comment'视频数据表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bili_video');
    }
}
