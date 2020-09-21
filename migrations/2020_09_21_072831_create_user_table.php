<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('username', '25')->comment('用户名');
            $table->string('password', '100')->comment('账号密码');
            $table->string('desc', '255')->default('')->comment('描述，备注');
            $table->tinyinteger('status')->default(1)->comment('账号状态 0-停用，1-启用');
            $table->string('avatar', '255')->default('')->comment('用户头像');
            $table->string('mobile', '15')->default('')->comment('手机号码');
            $table->string('last_ip', '15')->default(0)->comment('上次登录IP');
            $table->string('creater', '100')->default('')->comment('创建者');
            $table->integer('last_login')->default(0)->comment('上次登录时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
}
