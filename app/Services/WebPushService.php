<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected ?array $vapidKeys = null;

    public function __construct()
    {
        $this->vapidKeys = $this->resolveVapidKeys();
    }

    /**
     * Resolves or generates persistent VAPID keys.
     */
    protected function resolveVapidKeys(): array
    {
        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');
        $subject = env('VAPID_SUBJECT', config('app.url', 'https://tokenglade.com'));

        if (!empty($publicKey) && !empty($privateKey)) {
            return [
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
                'subject' => $subject,
            ];
        }

        $keysFile = storage_path('app/vapid_keys.json');
        if (file_exists($keysFile)) {
            $data = json_decode(file_get_contents($keysFile), true);
            if (!empty($data['publicKey']) && !empty($data['privateKey'])) {
                return [
                    'publicKey' => $data['publicKey'],
                    'privateKey' => $data['privateKey'],
                    'subject' => $subject,
                ];
            }
        }

        try {
            $newKeys = $this->generateVapidKeys();
            if (!file_exists(dirname($keysFile))) {
                @mkdir(dirname($keysFile), 0755, true);
            }
            file_put_contents($keysFile, json_encode($newKeys, JSON_PRETTY_PRINT));

            return [
                'publicKey' => $newKeys['publicKey'],
                'privateKey' => $newKeys['privateKey'],
                'subject' => $subject,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to create VAPID keys: ' . $e->getMessage());
            return [
                'publicKey' => '',
                'privateKey' => '',
                'subject' => $subject,
            ];
        }
    }

    /**
     * Generate VAPID EC P-256 keypair with Windows/Linux compatibility.
     */
    protected function generateVapidKeys(): array
    {
        try {
            return VAPID::createVapidKeys();
        } catch (\Throwable $e) {
            // Fallback for Windows environments where OpenSSL requires explicit cnf config
            $possibleCnfPaths = [
                'C:\\laragon\\bin\\php\\php-8.2.28-Win32-vs16-x64\\extras\\ssl\\openssl.cnf',
                'C:\\laragon\\bin\\apache\\httpd-2.4.54-win64-VS16\\conf\\openssl.cnf',
                'C:\\laragon\\bin\\git\\mingw64\\ssl\\openssl.cnf',
            ];
            $cnf = null;
            foreach ($possibleCnfPaths as $path) {
                if (file_exists($path)) {
                    $cnf = $path;
                    break;
                }
            }

            $config = [
                'curve_name' => 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC,
            ];
            if ($cnf) {
                $config['config'] = $cnf;
            }

            $key = openssl_pkey_new($config);
            if (!$key) {
                throw new \RuntimeException('Failed to generate EC key with OpenSSL: ' . openssl_error_string());
            }

            $details = openssl_pkey_get_details($key);
            if (!$details || empty($details['ec'])) {
                throw new \RuntimeException('Failed to extract EC key details from OpenSSL.');
            }

            $x = $details['ec']['x'];
            $y = $details['ec']['y'];
            $d = $details['ec']['d'];

            $publicKeyBinary = "\x04" . str_pad($x, 32, "\0", STR_PAD_LEFT) . str_pad($y, 32, "\0", STR_PAD_LEFT);
            $privateKeyBinary = str_pad($d, 32, "\0", STR_PAD_LEFT);

            return [
                'publicKey' => \Jose\Component\Core\Util\Base64UrlSafe::encodeUnpadded($publicKeyBinary),
                'privateKey' => \Jose\Component\Core\Util\Base64UrlSafe::encodeUnpadded($privateKeyBinary),
            ];
        }
    }

    /**
     * Get VAPID public key for browser PushManager subscription.
     */
    public function getPublicKey(): string
    {
        return $this->vapidKeys['publicKey'] ?? '';
    }

    /**
     * Send Web Push notification to a specific push subscription.
     */
    public function sendNotification(PushSubscription $subModel, array $payload): bool
    {
        if (empty($this->vapidKeys['publicKey']) || empty($this->vapidKeys['privateKey'])) {
            Log::warning('Cannot send push notification: VAPID keys missing.');
            return false;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => $this->vapidKeys['subject'],
                    'publicKey' => $this->vapidKeys['publicKey'],
                    'privateKey' => $this->vapidKeys['privateKey'],
                ],
            ];

            $webPush = new WebPush($auth);
            $webPush->setReuseVAPIDHeaders(true);

            $subscription = Subscription::create([
                'endpoint' => $subModel->endpoint,
                'publicKey' => $subModel->keys_p256dh,
                'authToken' => $subModel->keys_auth,
            ]);

            $jsonPayload = json_encode([
                'title' => $payload['title'] ?? 'TokenGlade Price Alert',
                'body'  => $payload['body'] ?? $payload['message'] ?? 'A price alert has been triggered on TokenGlade.',
                'icon'  => $payload['icon'] ?? '/src/assets/token-glade-logo.png',
                'badge' => $payload['badge'] ?? '/src/assets/token-glade-logo.png',
                'tag'   => $payload['tag'] ?? 'price-alert-' . time(),
                'url'   => $payload['url'] ?? 'https://tokenglade.com',
                'data'  => [
                    'url' => $payload['url'] ?? 'https://tokenglade.com',
                    'asset_code' => $payload['asset_code'] ?? null,
                    'asset_issuer' => $payload['asset_issuer'] ?? null,
                ],
            ]);

            $report = $webPush->sendOneNotification($subscription, $jsonPayload);

            if (!$report->isSuccess()) {
                Log::warning("Web Push delivery failed to {$subModel->endpoint}: " . $report->getReason());
                if ($report->isSubscriptionExpired()) {
                    $subModel->delete();
                }
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WebPush send error: ' . $e->getMessage());
            return false;
        }
    }
}
