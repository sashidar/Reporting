<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'settings',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('publishing_type')->default('unsegmented');
                $table->json('registry_info')->nullable();
                $table->json('default_field_values')->nullable();
                $table->json('default_field_groups')->nullable();
                $table->string('version', 16)->default('2.01');
                $table->integer('organization_id');
                $table->timestamps();

                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settings');
    }

}
