<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePhotoAlbumTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photo_album', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('album_name', 191)->default('')->comment('相册名');
            $table->string('album_desc', 500)->default('')->comment('相册描述');
            $table->string('album_cover', 500)->default('')->comment('相册封面图');
            $table->integer('album_type')->default('0')->comment('相册分类');
            $table->string('album_author', 255)->default('0')->comment('相册作者');
            $table->integer('album_click_num')->default('0')->comment('相册浏览数');
            $table->tinyInteger('album_status')->default('1')->comment('相册状态 1：启用 0：禁用');
            $table->string('album_question', 500)->default('1')->comment('访问相册的问题');
            $table->string('album_answer', 500)->default('1')->comment('访问相册的密码');
            $table->integer('album_sort')->default('99')->comment('相册排序 数字越小越靠前');
            $table->timestamps();
        });
        \Hyperf\DbConnection\Db::statement("ALTER TABLE `".(env('DB_PREFIX','')?:'')."photo_album` comment'相册'");//表注释一定加上前缀
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_album');
    }
}
