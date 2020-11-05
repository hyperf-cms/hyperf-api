<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title', '100')->default('')->comment('菜单标题');
            $table->string('app', '100')->default('')->comment('菜单标识')->unique();
            $table->integer('parent_id')->default(0)->comment('父级ID');
            $table->string('path', '100')->default('')->comment('路由路径');
            $table->string('icon', '100')->default('')->comment('图标');
            $table->tinyinteger('status')->default(1)->comment('账号状态 0-停用，1-启用');
            $table->integer('sort')->default(99)->comment('菜单顺序，数字越小越靠前');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
}
