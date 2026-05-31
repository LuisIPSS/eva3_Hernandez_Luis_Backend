<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->index(['activo', 'validado'], 'personas_activo_validado_index');
            $table->index(['activo', 'nivel_educacional'], 'personas_activo_nivel_educacional_index');
            $table->index('validado', 'personas_validado_index');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->index(['activo', 'tipo_empresa'], 'empresas_activo_tipo_empresa_index');
            $table->index(['activo', 'validado'], 'empresas_activo_validado_index');
            $table->index('validado', 'empresas_validado_index');
        });

        Schema::table('contactos_solicitados', function (Blueprint $table) {
            $table->index('estado', 'contactos_estado_index');
            $table->index(['estado', 'created_at'], 'contactos_estado_created_at_index');
            $table->index(['empresa_id', 'persona_id', 'estado'], 'contactos_empresa_persona_estado_index');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('personas_activo_validado_index');
            $table->dropIndex('personas_activo_nivel_educacional_index');
            $table->dropIndex('personas_validado_index');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropIndex('empresas_activo_tipo_empresa_index');
            $table->dropIndex('empresas_activo_validado_index');
            $table->dropIndex('empresas_validado_index');
        });

        Schema::table('contactos_solicitados', function (Blueprint $table) {
            $table->dropIndex('contactos_estado_index');
            $table->dropIndex('contactos_estado_created_at_index');
            $table->dropIndex('contactos_empresa_persona_estado_index');
        });
    }
};
