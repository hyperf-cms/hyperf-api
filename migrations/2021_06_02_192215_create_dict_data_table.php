<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateDictDataTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dict_data', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('dict_code')->comment('字典编码');
            $table->integer('user_id')->default('0')->comment('字典排序');
            $table->string('dict_label', 100)->comment('字典标签');
            $table->string('dict_value', 100)->comment('字典键值');
            $table->string('dict_type', 100)->comment('字典类型');
            $table->string('css_class', 100)->comment('样式属性（其他样式扩展）');
            $table->string('list_class', 100)->comment('表格回显样式');
            $table->tinyInteger('is_default')->default('1')->comment('是否默认（Y是 N否）');
            $table->tinyInteger('status')->default('1')->comment('状态（0正常 1停用）');
            $table->string('remark', 500)->comment('备注');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `advice` comment'字典数据表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dict_data');
    }
}
