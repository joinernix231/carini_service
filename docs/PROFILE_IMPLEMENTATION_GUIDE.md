# 🔧 Guía de Implementación - Sistema de Perfiles

## 📋 Resumen Técnico

Sistema de actualización de perfiles de usuario con **patrón repositorio** que maneja diferentes roles (cliente, técnico, coordinador) con transacciones automáticas y validaciones específicas por rol.

## 🎯 Objetivo

Permitir que usuarios actualicen su información personal según su rol, manteniendo la integridad de datos y siguiendo el patrón repositorio.

## 🏗️ Arquitectura Implementada

```
ProfileAPIController
├── ClientRepository (para rol 'cliente')
├── TechnicianRepository (para rol 'tecnico') 
├── CoordinatorRepository (para rol 'coordinador')
└── UserRepository (para datos básicos)
```

## 📁 Archivos a Crear/Modificar

### 1. Controlador Principal
**Archivo:** `app/Http/Controllers/Auth/ProfileAPIController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileAPIRequest;
use App\Models\User;
use App\Repositories\Client\ClientRepository;
use App\Repositories\Coordinator\CoordinatorRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Repositories\User\UserRepository;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;

class ProfileAPIController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private ClientRepository $clientRepository,
        private TechnicianRepository $technicianRepository,
        private CoordinatorRepository $coordinatorRepository
    ) {}

    public function update(UpdateProfileAPIRequest $request): JsonResponse
    {
        /**@var User $user*/
        $user = auth()->user();

        try {
            switch ($user->role) {
                case 'cliente':
                    $this->updateClientProfile($user, $request);
                    break;
                case 'tecnico':
                    $this->updateTechnicianProfile($user, $request);
                    break;
                case 'coordinador':
                    $this->updateCoordinatorProfile($user, $request);
                    break;
                default:
                    $this->updateUserProfile($user, $request);
                    break;
            }

            return ResponseUtil::makeResponse('Perfil actualizado correctamente.');

        } catch (\Exception $e) {
            return ResponseUtil::makeError('Error al actualizar el perfil: ' . $e->getMessage(), 500);
        }
    }

    private function updateClientProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $clientData = $request->only([
            'name', 'email', 'address', 'city', 'phone', 'document'
        ]);

        $this->clientRepository->updateProfile($user->id, $clientData);
    }

    private function updateTechnicianProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $userData = $request->only(['name', 'email']);
        $technicianData = $request->only(['document', 'phone', 'address']);

        $this->technicianRepository->updateFullProfile($user->id, $userData, $technicianData);
    }

    private function updateCoordinatorProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $userData = $request->only(['name', 'email']);
        $coordinatorData = $request->only(['identification', 'phone', 'address']);

        $this->coordinatorRepository->updateFullProfile($user->id, $userData, $coordinatorData);
    }

    private function updateUserProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $userData = $request->only(['name', 'email']);
        $this->userRepository->updateProfile($user->id, $userData);
    }
}
```

### 2. Request de Validación
**Archivo:** `app/Http/Requests/Auth/UpdateProfileAPIRequest.php`

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
        ];

        // Reglas específicas según el rol
        switch ($user->role) {
            case 'cliente':
                $rules['document'] = 'sometimes|string|max:20';
                break;
            case 'tecnico':
                $rules['document'] = 'sometimes|string|max:20';
                break;
            case 'coordinador':
                $rules['identification'] = 'sometimes|integer|unique:coordinators,identification,' . ($user->coordinator?->id ?? 'NULL');
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está en uso por otro usuario.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'city.string' => 'La ciudad debe ser una cadena de texto.',
            'city.max' => 'La ciudad no puede tener más de 255 caracteres.',
            'document.string' => 'El documento debe ser una cadena de texto.',
            'document.max' => 'El documento no puede tener más de 20 caracteres.',
            'identification.integer' => 'La identificación debe ser un número entero.',
            'identification.unique' => 'Esta identificación ya está en uso por otro coordinador.',
        ];
    }
}
```

### 3. Repositorios a Implementar

#### ClientRepository
**Archivo:** `app/Repositories/Client/ClientRepository.php`

```php
// Agregar estos métodos al ClientRepository existente:

public function getClientByUserId(int $id)
{
    return $this->model
        ->where('user_id', $id)
        ->first();
}

public function updateProfile(int $userId, array $attributes): Client
{
    return \DB::transaction(function () use ($userId, $attributes) {
        $client = $this->getClientByUserId($userId);
        
        if (!$client) {
            throw new \Exception('No se encontró el perfil del cliente');
        }

        // Si se proporciona document, actualizar identifier
        if (isset($attributes['document'])) {
            $attributes['identifier'] = $attributes['document'];
            unset($attributes['document']);
        }

        return $this->update($attributes, $client->id);
    });
}
```

#### TechnicianRepository
**Archivo:** `app/Repositories/Technician/TechnicianRepository.php`

```php
// Agregar estos métodos al TechnicianRepository existente:

public function getTechnicianByUserId(int $userId): ?Technician
{
    return $this->model
        ->where('user_id', $userId)
        ->first();
}

public function updateProfile(int $userId, array $attributes): Technician
{
    return \DB::transaction(function () use ($userId, $attributes) {
        $technician = $this->getTechnicianByUserId($userId);
        
        if (!$technician) {
            throw new \Exception('No se encontró el perfil del técnico');
        }

        return $this->update($attributes, $technician->id);
    });
}

public function updateFullProfile(int $userId, array $userAttributes, array $technicianAttributes): array
{
    return \DB::transaction(function () use ($userId, $userAttributes, $technicianAttributes) {
        $results = [];
        
        // Actualizar datos del usuario si hay atributos
        if (!empty(array_filter($userAttributes))) {
            $userRepo = app(\App\Repositories\User\UserRepository::class);
            $results['user'] = $userRepo->updateProfile($userId, $userAttributes);
        }
        
        // Actualizar datos del técnico si hay atributos
        if (!empty(array_filter($technicianAttributes))) {
            $results['technician'] = $this->updateProfile($userId, $technicianAttributes);
        }
        
        return $results;
    });
}
```

#### CoordinatorRepository
**Archivo:** `app/Repositories/Coordinator/CoordinatorRepository.php`

```php
// Agregar estos métodos al CoordinatorRepository existente:

public function getCoordinatorByUserId(int $userId): ?Coordinator
{
    return $this->model
        ->where('user_id', $userId)
        ->first();
}

public function updateProfile(int $userId, array $attributes): Coordinator
{
    return \DB::transaction(function () use ($userId, $attributes) {
        $coordinator = $this->getCoordinatorByUserId($userId);
        
        if (!$coordinator) {
            throw new \Exception('No se encontró el perfil del coordinador');
        }

        return $this->update($attributes, $coordinator->id);
    });
}

public function updateFullProfile(int $userId, array $userAttributes, array $coordinatorAttributes): array
{
    return \DB::transaction(function () use ($userId, $userAttributes, $coordinatorAttributes) {
        $results = [];
        
        // Actualizar datos del usuario si hay atributos
        if (!empty(array_filter($userAttributes))) {
            $userRepo = app(\App\Repositories\User\UserRepository::class);
            $results['user'] = $userRepo->updateProfile($userId, $userAttributes);
        }
        
        // Actualizar datos del coordinador si hay atributos
        if (!empty(array_filter($coordinatorAttributes))) {
            $results['coordinator'] = $this->updateProfile($userId, $coordinatorAttributes);
        }
        
        return $results;
    });
}
```

#### UserRepository
**Archivo:** `app/Repositories/User/UserRepository.php`

```php
// Agregar este método al UserRepository existente:

public function updateProfile(int $userId, array $attributes): User
{
    return \DB::transaction(function () use ($userId, $attributes) {
        $user = $this->find($userId);
        
        if (!$user) {
            throw new \Exception('No se encontró el usuario');
        }

        return $this->update($attributes, $userId);
    });
}
```

### 4. Rutas
**Archivo:** `routes/api.php`

```php
// Agregar estas rutas:

use App\Http\Controllers\Auth\ProfileAPIController;

// Dentro del grupo de rutas:
Route::put('/profile', ProfileAPIController::class . '@update');
```

## 🔄 Lógica de Actualización por Rol

### Cliente (rol: 'cliente')
- **Tabla:** `clients`
- **Campos:** `name`, `email`, `address`, `city`, `phone`, `document` (→ `identifier`)
- **Método:** `ClientRepository::updateProfile()`

### Técnico (rol: 'tecnico')
- **Tablas:** `users` + `technicians`
- **En users:** `name`, `email`
- **En technicians:** `document`, `phone`, `address`
- **Método:** `TechnicianRepository::updateFullProfile()`

### Coordinador (rol: 'coordinador')
- **Tablas:** `users` + `coordinators`
- **En users:** `name`, `email`
- **En coordinators:** `identification`, `phone`, `address`
- **Método:** `CoordinatorRepository::updateFullProfile()`

## 🎯 Puntos Clave de Implementación

### 1. Transacciones Automáticas
- ✅ Cada repositorio maneja sus propias transacciones con `\DB::transaction()`
- ✅ Rollback automático si algo falla
- ✅ No hay que manejar `DB::beginTransaction()` manualmente

### 2. Validaciones Específicas por Rol
- ✅ Cliente/Técnico: `document` (string, max:20)
- ✅ Coordinador: `identification` (integer, unique)
- ✅ Todos: `name`, `email`, `phone`, `address`, `city`

### 3. Manejo de Errores
- ✅ Excepciones específicas por repositorio
- ✅ Respuestas HTTP apropiadas
- ✅ Mensajes de error en español

### 4. Inyección de Dependencias
- ✅ Constructor del controlador recibe todos los repositorios
- ✅ Fácil testing y mocking
- ✅ Separación clara de responsabilidades

## 🧪 Testing

### Ejemplo de Test Unitario
```php
public function test_update_client_profile()
{
    $user = User::factory()->create(['role' => 'cliente']);
    $client = Client::factory()->create(['user_id' => $user->id]);
    
    $response = $this->putJson('/api/profile', [
        'name' => 'Nuevo Nombre',
        'email' => 'nuevo@email.com',
        'phone' => '123456789'
    ]);
    
    $response->assertStatus(200);
    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'name' => 'Nuevo Nombre',
        'email' => 'nuevo@email.com'
    ]);
}
```

## 🚨 Consideraciones Importantes

1. **Transacciones:** Cada repositorio maneja sus propias transacciones
2. **Validaciones:** Específicas según el rol del usuario
3. **Errores:** Manejo centralizado en el controlador
4. **Testing:** Fácil mockear repositorios
5. **Mantenibilidad:** Código limpio y bien estructurado

## 📋 Checklist de Implementación

- [ ] Crear `ProfileAPIController`
- [ ] Crear `UpdateProfileAPIRequest`
- [ ] Agregar métodos a `ClientRepository`
- [ ] Agregar métodos a `TechnicianRepository`
- [ ] Agregar métodos a `CoordinatorRepository`
- [ ] Agregar métodos a `UserRepository`
- [ ] Agregar rutas en `api.php`
- [ ] Probar endpoints
- [ ] Escribir tests

---

**¡Implementación lista para usar!** 🚀
