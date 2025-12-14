<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // NOMBRE DE LA SUCURSAL 
            $table->string('code')->nullable()->unique(); // CODIGO INTERNO UNICO DE LA SUCURSAL, EJM BR001
            $table->text('address')->nullable();//DIRECCION COMPLETA DE LA SUCURSAL
            $table->string('phone')->nullable();//NUMERO DE TELEFONO DE CONTACTO
            $table->string('email')->nullable();//CORREO DE LA SUCURSAL 
            $table->unsignedBigInteger('manager_user_id')->nullable()->index(); //ID DEL USUARIO RESPONSABLE O GERENTE DE LA SUCURSAL


            // business-related fields
            $table->boolean('is_active')->default(true);//ESTADO DE LA SUCURSAR: ACTIVA O INACTIVA


            // auditing helpers
            $table->unsignedBigInteger('created_by')->nullable()->index();// ID DEL USUARIO QUE CREA EL REGISTRO 
            $table->unsignedBigInteger('updated_by')->nullable()->index();// ID DEL USUARIO QUE ACTUALIZO EL REGISTRO


            $table->timestamps();
            $table->softDeletes();


            //RELACIONES CON LA TABLA USERS
            $table->foreign('manager_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
