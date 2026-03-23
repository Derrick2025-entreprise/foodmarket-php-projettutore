<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthController — Gestion de l'authentification
 *
 * POST /api/auth/register  → Inscription
 * POST /api/auth/login     → Connexion + retour JWT
 */
class AuthController extends BaseController
{
    /**
     * POST /api/auth/register
     * Body JSON : { name, email, password }
     */
    public function register(): ResponseInterface
    {
        $model = new UserModel();
        $data  = $this->request->getJSON(true) ?? [];

        // Hasher le mot de passe avant insertion
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        // Forcer le rôle user par défaut (sécurité)
        $data['role'] = 'user';

        if (!$model->insert($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['errors' => $model->errors()]);
        }

        $user = $model->find($model->getInsertID());
        unset($user['password']);

        return $this->response->setStatusCode(201)
            ->setJSON(['message' => 'Compte créé avec succès', 'user' => $user]);
    }

    /**
     * POST /api/auth/login
     * Body JSON : { email, password }
     */
    public function login(): ResponseInterface
    {
        $data  = $this->request->getJSON(true) ?? [];
        $model = new UserModel();

        if (empty($data['email']) || empty($data['password'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Email et mot de passe requis']);
        }

        $user = $model->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            return $this->response->setStatusCode(401)
                ->setJSON(['error' => 'Identifiants incorrects']);
        }

        $token = $this->generateJwt($user);

        return $this->response->setJSON([
            'token' => $token,
            'user'  => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ]);
    }

    /**
     * Génère un JWT signé HMAC-SHA256
     */
    private function generateJwt(array $user): string
    {
        $secret = env('JWT_SECRET', 'foodmarket_secret_key_change_in_production');

        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'email'   => $user['email'],
            'role'    => $user['role'],
            'exp'     => time() + 86400, // expire dans 24h
        ]));

        // Encoder en base64url (remplacer +/ par -_)
        $header  = strtr(rtrim($header,  '='), '+/', '-_');
        $payload = strtr(rtrim($payload, '='), '+/', '-_');

        $signature = base64_encode(hash_hmac('sha256', "{$header}.{$payload}", $secret, true));
        $signature = strtr(rtrim($signature, '='), '+/', '-_');

        return "{$header}.{$payload}.{$signature}";
    }
}
