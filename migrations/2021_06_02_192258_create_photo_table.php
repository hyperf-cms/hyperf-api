<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePhotoTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photo', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->tinyInteger('photo_album')->comment('所属相册');
            $table->string('photo_url', 1000)->default('')->comment('图片路径');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `photo` comment'照片'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo');
    }
}
