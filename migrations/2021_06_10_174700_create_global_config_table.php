<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateGlobalConfigTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('global_config', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('key_name', 255)->default('')->comment('keyName');
            $table->string('name', 255)->default('')->comment('名称');
            $table->string('type', 50)->default('text')->comment('类型 text html json boolean');
            $table->string('remark', 1000)->default('')->comment('备注');
            $table->text('data')->comment('数据');
            $table->timestamps();
            $table->unique('key_name', 'key_name_unique');
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".(env('DB_PREFIX','')?:'')."global_config` comment'参数配置表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_config');
    }
}
