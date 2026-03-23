<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['name', 'email', 'password', 'role'];

    protected $useTimestamps = true;

    // Ne jamais retourner le mot de passe dans les réponses JSON
    protected $hidden = ['password'];

    protected $validationRules = [
        'name'     => 'required|min_length[2]|max_length[100]',
        'email'    => 'required|valid_email|max_length[150]|is_unique[users.email]',
        'password' => 'required|min_length[6]',
    ];

    protected $validationMessages = [
        'email'    => ['is_unique' => 'Cet email est déjà utilisé'],
        'password' => ['min_length' => 'Le mot de passe doit faire au moins 6 caractères'],
    ];

    /**
     * Trouve un utilisateur par email (pour le login)
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }
}
