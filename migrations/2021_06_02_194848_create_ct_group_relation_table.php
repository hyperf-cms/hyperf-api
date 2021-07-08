<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCtGroupRelationTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ct_group_relation', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('uid')->default('0')->comment('用户ID');
            $table->string('group_id', 50)->default('0')->comment('群ID');
            $table->tinyInteger('level')->default('2')->comment('级别：0群主，1管理员，2成员');
            $table->timestamps();
            $table->primary(['uid', 'group_id'], 'uid_group_id_key');
            $table->index('uid', 'uid_index');
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".(env('DB_PREFIX','')?:'')."ct_group_relation` comment'群组-用户关联表'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ct_group_relation');
    }
}
