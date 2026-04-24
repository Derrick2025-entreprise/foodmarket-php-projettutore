<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table      = 'categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['name', 'description', 'image'];

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
    ];
}
