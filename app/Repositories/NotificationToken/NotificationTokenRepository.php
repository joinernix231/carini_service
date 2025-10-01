<?php

namespace App\Repositories\NotificationToken;

use App\Models\NotificationToken;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class NotificationTokenRepository extends BaseRepository
{
    public function model()
    {
        return NotificationToken::class;
    }


    /**
     * Obtener tokens activos por usuario
     */
    public function getActiveTokensByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->active()
            ->get();
    }

    /**
     * Obtener tokens activos por usuarios
     */
    public function getActiveTokensByUsers(array $userIds): Collection
    {
        return $this->model
            ->whereIn('user_id', $userIds)
            ->active()
            ->get();
    }

    /**
     * Obtener tokens activos por plataforma
     */
    public function getActiveTokensByPlatform(string $platform): Collection
    {
        return $this->model
            ->active()
            ->platform($platform)
            ->get();
    }

    /**
     * Registrar o actualizar token
     */
    public function registerOrUpdateToken(array $data): NotificationToken
    {
        $existingToken = $this->model->where('token', $data['token'])->first();

        if ($existingToken) {
            $existingToken->update([
                'user_id' => $data['user_id'],
                'device_id' => $data['device_id'],
                'platform' => $data['platform'],
                'app_version' => $data['app_version'] ?? null,
                'device_model' => $data['device_model'] ?? null,
                'os_version' => $data['os_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now()
            ]);

            return $existingToken->fresh();
        }

        return $this->create($data);
    }

    /**
     * Desactivar token específico
     */
    public function deactivateToken(int $userId, string $token): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('token', $token)
            ->update(['is_active' => false]);
    }

    /**
     * Desactivar todos los tokens del usuario
     */
    public function deactivateAllUserTokens(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->update(['is_active' => false]);
    }

    /**
     * Obtener tokens del usuario con información básica
     */
    public function getUserTokens(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->active()
            ->select(['id', 'token', 'platform', 'device_model', 'last_used_at', 'created_at'])
            ->get();
    }

    /**
     * Obtener estadísticas de tokens
     */
    public function getTokenStats(): array
    {
        $totalTokens = $this->model->count();
        $activeTokens = $this->model->active()->count();
        $tokensByPlatform = $this->model->active()
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        return [
            'total_tokens' => $totalTokens,
            'active_tokens' => $activeTokens,
            'inactive_tokens' => $totalTokens - $activeTokens,
            'tokens_by_platform' => $tokensByPlatform
        ];
    }

    /**
     * Limpiar tokens inactivos antiguos
     */
    public function cleanupInactiveTokens(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return $this->model
            ->where('is_active', false)
            ->where('updated_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Obtener tokens activos para envío de notificaciones
     */
    public function getActiveTokensForSending(array $userIds = null): array
    {
        $query = $this->model->active();

        if ($userIds) {
            $query->whereIn('user_id', $userIds);
        }

        return $query->pluck('token')->toArray();
    }

    /**
     * Marcar token como usado
     */
    public function markTokenAsUsed(string $token): bool
    {
        return $this->model
            ->where('token', $token)
            ->update(['last_used_at' => now()]);
    }

    /**
     * Verificar si usuario tiene tokens activos
     */
    public function userHasActiveTokens(int $userId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->active()
            ->exists();
    }

    /**
     * Obtener tokens por dispositivo
     */
    public function getTokensByDevice(string $deviceId): Collection
    {
        return $this->model
            ->where('device_id', $deviceId)
            ->active()
            ->get();
    }


}
