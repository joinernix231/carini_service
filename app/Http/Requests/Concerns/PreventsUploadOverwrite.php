<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * El nombre del archivo lo elige el cliente (la app ya le añade timestamp y un
 * sufijo aleatorio). Para no cambiar ese contrato, se rechaza la subida si ya
 * existe un objeto con ese nombre en S3: así nadie puede sobrescribir la firma,
 * las fotos o el soporte de pago de otro usuario.
 */
trait PreventsUploadOverwrite
{
    protected function rejectIfExists($validator, callable $pathFor): void
    {
        $validator->after(function ($validator) use ($pathFor) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $name = (string) $this->input('name');

            try {
                $exists = Storage::disk('s3')->exists($pathFor($name));
            } catch (\Throwable $e) {
                // Si no se puede comprobar (permisos/red), no se bloquea la subida.
                Log::warning('No se pudo comprobar si el archivo ya existe en S3', ['error' => $e->getMessage()]);
                return;
            }

            if ($exists) {
                $validator->errors()->add('name', 'Ya existe un archivo con ese nombre');
            }
        });
    }
}
