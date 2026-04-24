<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Recrée la table products avec category_id (FK vers categories)
 * et les champs complets demandés.
 */
class AlterProductsAddCategoryId extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne table products si elle existe
        if ($this->db->tableExists('products')) {
            $this->forge->dropTable('products', true);
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'stock' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('products');
    }

    public function down(): void
    {
        $this->forge->dropTable('products');
    }
}
