<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCtGroupTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ct_group', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('group_id', 50)->comment('组ID');
            $table->integer('uid', 11)->default('0')->comment('创建群用户ID');
            $table->string('group_name', 30)->default('0')->comment('群名称');
            $table->string('avatar', 255)->default('')->comment('头像');
            $table->integer('size', 11)->default('200')->comment('群规模 200 500 1000');
            $table->text('introduction')->comment('群介绍');
            $table->tinyInteger('validation')->default('1')->comment('加群是否需要验证 0 不需要 1需要');
            $table->timestamps();
            $table->index('uid', 'uid_index');
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `ct_group` comment'群组表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ct_group');
    }
}
