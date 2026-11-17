# Notificaciones Web Push para la PWA

La app nativa recibe avisos por Expo/FCM. La PWA (la app abierta en el
navegador o instalada en la pantalla de inicio) los recibe por **Web Push**,
usando el paquete `laravel-notification-channels/webpush`.

## Cómo funciona

1. En la PWA el usuario toca **"Activar notificaciones"**. El navegador pide
   permiso y crea una suscripción (`PushManager.subscribe`) con la clave VAPID
   pública.
2. La PWA manda la suscripción a `POST /api/notifications/web-push/subscribe`.
   Se guarda en la tabla `push_subscriptions`, ligada al usuario.
3. Todas las notificaciones que ya usaban el canal `expo` (por ejemplo
   `MantenimientoAsignado`) salen además por Web Push: `App\Broadcasting\ExpoChannel`
   toma el mismo `toExpo()` y lo envía a los navegadores suscritos. No hay que
   tocar cada notificación.
4. El service worker de la PWA (`public/sw.js` en carini_app) muestra la
   notificación y, al tocarla, abre o enfoca la app.

Si un navegador cancela la suscripción (respuesta 404/410), el paquete la borra
solo.

## Endpoints (requieren sesión, igual que el resto de la API)

| Método | Ruta | Cuerpo |
| --- | --- | --- |
| GET | `/api/notifications/web-push/public-key` | — (devuelve `data.public_key`) |
| POST | `/api/notifications/web-push/subscribe` | `PushSubscription.toJSON()`: `{ endpoint, keys: { p256dh, auth } }` y opcional `content_encoding` (`aes128gcm` por defecto) |
| DELETE | `/api/notifications/web-push/unsubscribe` | `{ endpoint }` |

Si el mismo navegador se suscribe con otro usuario (cambio de sesión), la
suscripción pasa al último usuario.

## Configuración (por servidor)

1. Generar las llaves **una sola vez por servidor**:

   ```bash
   php artisan webpush:vapid --show
   ```

2. Ponerlas en el `.env` del servidor (**nunca en Git**):

   ```
   VAPID_SUBJECT=mailto:correo-de-contacto@dominio
   VAPID_PUBLIC_KEY=...
   VAPID_PRIVATE_KEY=...
   ```

3. `php artisan migrate` (crea `push_subscriptions`) y `php artisan config:clear`.
4. Compilar la PWA con la **misma** clave pública:
   `EXPO_PUBLIC_VAPID_PUBLIC_KEY=... npx expo export -p web`. Si no se pone,
   la PWA la pide a `/api/notifications/web-push/public-key`.

Si se cambian las llaves, las suscripciones existentes dejan de servir: cada
usuario debe volver a tocar "Activar notificaciones".

Sin llaves VAPID el canal simplemente no envía Web Push (la app nativa sigue
igual).

## Limitaciones de iPhone

- Solo funciona en **iOS / iPadOS 16.4 o superior**.
- Solo funciona con la PWA **agregada a la pantalla de inicio** (Safari →
  Compartir → "Agregar a inicio") y abierta desde ese ícono. En una pestaña
  normal de Safari no existe `PushManager`.
- El permiso solo se puede pedir después de que el usuario toque un botón; por
  eso la PWA muestra "Activar notificaciones" en lugar de pedirlo sola.

En Android (Chrome, Edge, Samsung Internet) y en escritorio funciona también
desde el navegador sin instalar.

## Notas

- `minishlink/web-push` necesita las extensiones `openssl`, `curl` y
  `mbstring` (ya presentes en la imagen Docker). `gmp`/`bcmath` son opcionales.
- En Windows, `webpush:vapid` puede fallar con "Unable to create the key" si
  PHP no encuentra `openssl.cnf`; definir `OPENSSL_CONF` apuntando a
  `extras/ssl/openssl.cnf` de la instalación de PHP.
