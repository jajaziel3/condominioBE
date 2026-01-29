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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->string('telefono', 20)->nullable();
            $table->foreignId('id_departamento')
                ->nullable()
                ->constrained('departamentos', 'id_departamento')
                ->onDelete('set null');
            $table->enum('rol', ['residente', 'administrador', 'seguridad'])->default('residente');
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
