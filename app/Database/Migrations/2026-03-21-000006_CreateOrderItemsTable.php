<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderItemsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'product_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'quantity' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('order_id',   'orders',   'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('order_items');
    }

    public function down(): void
    {
        $this->forge->dropTable('order_items');
    }
}
