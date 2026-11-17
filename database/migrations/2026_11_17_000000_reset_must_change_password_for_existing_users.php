<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La app móvil y la PWA todavía no tienen pantalla de cambio de contraseña.
 * Si algún usuario queda con must_change_password = true, el middleware
 * RequirePasswordChange le bloquea todas las rutas (403) y no puede usar la app.
 * Esta migración deja a todos los usuarios existentes desbloqueados y el valor
 * por defecto en false. Cuando exista la pantalla en el frontend, se puede volver a activar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'must_change_password')) {
            DB::table('users')->update(['must_change_password' => false]);
        }
    }

    public function down(): void
    {
        // Sin reversa: no se vuelve a bloquear a los usuarios.
    }
};
