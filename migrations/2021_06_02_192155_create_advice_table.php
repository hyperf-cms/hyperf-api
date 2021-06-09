<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdviceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advice', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('用户ID');
            $table->string('title', 50)->comment('标题');
            $table->longText('content')->comment('内容');
            $table->tinyInteger('status')->default('0')->comment('状态（0：待解决，1：已解决，2：关闭）');
            $table->longText('reply')->comment('回复内容');
            $table->tinyInteger('type')->default('0')->comment('类型（0：bug，1：优化，2：混合');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `advice` comment'系统建议表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advice');
    }
}
