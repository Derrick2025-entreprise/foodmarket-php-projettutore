<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtre JWT — vérifie le token Bearer dans le header Authorization.
 * Utilisé pour protéger les routes /api/orders et /api/products (admin).
 *
 * Le token est un JWT signé avec HMAC-SHA256.
 * Payload attendu : { user_id, email, role, exp }
 */
class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Bypass complet en mode testing
        if (ENVIRONMENT === 'testing') {
            // Injecter un payload factice pour que les controllers fonctionnent
            $request->jwtPayload = ['user_id' => 1, 'email' => 'test@test.com', 'role' => 'admin'];
            return;
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token manquant ou invalide']);
        }

        $token = substr($authHeader, 7);

        try {
            $payload = $this->decodeJwt($token);
        } catch (\Exception $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => $e->getMessage()]);
        }

        // Vérifier l'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token expiré']);
        }

        // Vérifier le rôle admin si requis
        if ($arguments && in_array('admin', $arguments)) {
            if (($payload['role'] ?? '') !== 'admin') {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON(['error' => 'Accès réservé aux administrateurs']);
            }
        }

        // Injecter le payload dans la requête pour les controllers
        $request->jwtPayload = $payload;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien à faire après la requête
    }

    /**
     * Décode et vérifie un JWT (HMAC-SHA256) sans librairie externe.
     */
    private function decodeJwt(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \Exception('Format JWT invalide');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // Vérifier la signature
        $secret    = env('JWT_SECRET', 'foodmarket_secret_key_change_in_production');
        $expected  = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $secret, true);
        $signature = base64_decode(strtr($signatureB64, '-_', '+/'));

        if (!hash_equals($expected, $signature)) {
            throw new \Exception('Signature JWT invalide');
        }

        $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);

        if (!$payload) {
            throw new \Exception('Payload JWT invalide');
        }

        return $payload;
    }
}
