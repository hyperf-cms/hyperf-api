<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateLoginLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_log', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('username', 255)->default('')->comment('用户名称');
            $table->string('login_ip', 50)->default('')->comment('登陆IP地址');
            $table->string('login_address', 255)->default('')->comment('登陆地址');
            $table->string('login_browser', 255)->default('')->comment('登陆浏览器');
            $table->string('os', 50)->default('')->comment('操作系统');
            $table->string('response_result', 255)->default('')->comment('返回结果');
            $table->string('response_code', 50)->default('')->comment('返回状态码');
            $table->dateTime('login_date')->comment('登陆时间');
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".(env('DB_PREFIX','')?:'')."login_log` comment'登陆日志'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_log');
    }
}
