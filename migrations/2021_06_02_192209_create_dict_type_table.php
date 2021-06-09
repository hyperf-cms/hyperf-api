<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDictTypeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dict_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('dict_id')->comment('字典主键');
            $table->string('dict_name', 100)->comment('字典名称');
            $table->string('dict_type', 100)->comment('字典类型');
            $table->string('remark', 500)->comment('备注');
            $table->tinyInteger('status')->default('0')->comment('状态（0正常 1停用）');
            $table->unique('dict_type', 'dict_type');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `dict_type` comment'字典类型表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dict_type');
    }
}
