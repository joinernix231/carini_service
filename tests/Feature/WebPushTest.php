<?php

namespace Tests\Feature;

use App\Broadcasting\ExpoChannel;
use App\Http\Middleware\RequirePasswordChange;
use App\Http\Middleware\ValidateUserToken;
use App\Models\User;
use App\Notifications\Channels\ExpoMessageAsWebPush;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Mockery;
use NotificationChannels\WebPush\PushSubscription;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\TestCase;

/**
 * Corre sobre SQLite en memoria con solo las tablas que usa Web Push,
 * para no depender de MySQL.
 */
class WebPushTest extends TestCase
{
    private const ENDPOINT = 'https://fcm.googleapis.com/fcm/send/abc123';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'webpush.database_connection' => 'sqlite',
            'webpush.vapid.public_key' => 'test-public-key',
            'webpush.vapid.private_key' => 'test-private-key',
            'jwt.secret' => str_repeat('t', 64),
        ]);

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2025_10_01_060650_create_notification_tokens_table.php',
            '2026_11_17_120000_create_push_subscriptions_table.php',
        ] as $file) {
            (require database_path("migrations/{$file}"))->up();
        }
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', fn ($t) => $t->softDeletes());
        }
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', fn ($t) => $t->string('role')->nullable());
        }

        $this->withoutMiddleware([ValidateUserToken::class, RequirePasswordChange::class]);
    }

    private function makeUser(string $email): User
    {
        return User::forceCreate([
            'name' => 'Técnico',
            'email' => $email,
            'password' => 'secret123',
            'role' => 'tecnico',
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'endpoint' => self::ENDPOINT,
            'keys' => ['p256dh' => 'BPublicKey', 'auth' => 'AuthSecret'],
        ], $override);
    }

    public function test_guarda_y_borra_la_suscripcion_del_usuario(): void
    {
        $user = $this->makeUser('tec@example.com');

        $this->actingAs($user, 'api')
            ->postJson('/api/notifications/web-push/subscribe', $this->payload())
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, $user->pushSubscriptions()->count());
        $sub = $user->pushSubscriptions()->first();
        $this->assertSame('BPublicKey', $sub->public_key);
        $this->assertSame('aes128gcm', $sub->content_encoding);

        $this->actingAs($user, 'api')
            ->deleteJson('/api/notifications/web-push/unsubscribe', ['endpoint' => self::ENDPOINT])
            ->assertOk();

        $this->assertSame(0, PushSubscription::count());
    }

    public function test_el_mismo_navegador_pasa_al_ultimo_usuario_que_inicia_sesion(): void
    {
        $a = $this->makeUser('a@example.com');
        $b = $this->makeUser('b@example.com');

        $this->actingAs($a, 'api')->postJson('/api/notifications/web-push/subscribe', $this->payload())->assertOk();
        $this->actingAs($b, 'api')->postJson('/api/notifications/web-push/subscribe', $this->payload())->assertOk();

        $this->assertSame(0, $a->pushSubscriptions()->count());
        $this->assertSame(1, $b->pushSubscriptions()->count());
    }

    public function test_rechaza_suscripcion_invalida(): void
    {
        $user = $this->makeUser('tec@example.com');

        $this->actingAs($user, 'api')
            ->postJson('/api/notifications/web-push/subscribe', ['endpoint' => 'http://inseguro.test/x'])
            ->assertStatus(400); // APIRequest responde 400 a errores de validación

        $this->assertSame(0, PushSubscription::count());
    }

    public function test_canal_expo_tambien_envia_web_push_aunque_no_haya_tokens_expo(): void
    {
        $user = $this->makeUser('tec@example.com');
        $user->updatePushSubscription(self::ENDPOINT, 'BPublicKey', 'AuthSecret', 'aes128gcm');

        $channel = Mockery::mock(WebPushChannel::class);
        $channel->shouldReceive('send')->once()->withArgs(function ($notifiable, $notification) use ($user) {
            $message = $notification->toWebPush($notifiable)->toArray();

            return $notifiable->is($user)
                && $notification instanceof ExpoMessageAsWebPush
                && $message['title'] === '🔧 Mantenimiento Asignado'
                && $message['data']['type'] === 'maintenance_assigned'
                && $message['tag'] === 'maintenance_assigned-7';
        });
        $this->app->instance(WebPushChannel::class, $channel);

        $notification = new class extends Notification {
            public function toExpo($notifiable): array
            {
                return [
                    'title' => '🔧 Mantenimiento Asignado',
                    'body' => 'Se te ha asignado un mantenimiento',
                    'data' => ['type' => 'maintenance_assigned', 'maintenance_id' => 7, 'priority' => 'high'],
                ];
            }
        };

        $this->app->make(ExpoChannel::class)->send($user, $notification);
    }

    public function test_sin_llaves_vapid_no_intenta_web_push(): void
    {
        config(['webpush.vapid.private_key' => null]);
        $user = $this->makeUser('tec@example.com');
        $user->updatePushSubscription(self::ENDPOINT, 'BPublicKey', 'AuthSecret', 'aes128gcm');

        $channel = Mockery::mock(WebPushChannel::class);
        $channel->shouldNotReceive('send');
        $this->app->instance(WebPushChannel::class, $channel);

        $notification = new class extends Notification {
            public function toExpo($notifiable): array
            {
                return ['title' => 't', 'body' => 'b', 'data' => []];
            }
        };

        $this->app->make(ExpoChannel::class)->send($user, $notification);
        $this->addToAssertionCount(1);
    }
}
