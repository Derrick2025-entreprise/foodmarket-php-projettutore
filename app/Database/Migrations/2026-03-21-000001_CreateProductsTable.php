<?php

/**
 * @file CreateProductsTable.php
 * @description Migration : création de la table products
 * Exécuter avec : php spark migrate
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'prix' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'categorie' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'stock' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('products');
    }

    public function down(): void
    {
        // Rollback : supprime la table
        $this->forge->dropTable('products');
    }
}
