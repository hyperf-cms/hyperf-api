<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateNoticeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notice', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('username', 255)->comment('用户名称');
            $table->integer('user_id', 11)->comment('用户ID');
            $table->string('title', 50)->comment('标题');
            $table->longText('content')->comment('内容');
            $table->integer('public_time', 11)->default('0')->comment('发布时间');
            $table->tinyInteger('status', 4)->default('0')->comment('操作系统');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `notice` comment'系统通知表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice');
    }
}