<?php namespace Myth\Auth\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_auth_tables extends Migration
{
    public function up()
    {
        /*
         * Users
         */
        $this->forge->addField([
            'id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'            => ['type' => 'varchar', 'constraint' => 255],
            'username'         => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'name'             => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'password_hash'    => ['type' => 'varchar', 'constraint' => 255],
            'reset_hash'       => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'activate_hash'    => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status'           => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status_message'   => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'active'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'force_pass_reset' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'deleted'          => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'created_at'       => ['type' => 'datetime', 'null' => true],
            'updated_at'       => ['type' => 'datetime', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('email');

        $this->forge->createTable('users', true);

        /*
         * Auth Login Attempts
         */
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'email'      => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Only for successful logins
            'datetime'   => ['type' => 'datetime'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->addKey('user_id');
        // NOTE: Do NOT delete the user_id or email when the user is deleted for security audits
        $this->forge->createTable('auth_logins', true);

        /*
         * Auth Tokens
         * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
         */
        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'selector'        => ['type' => 'char', 'constraint' => 12],
            'hashedValidator' => ['type' => 'char', 'constraint' => 64],
            'user_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'expires'         => ['type' => 'datetime'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('selector');
        $this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
        $this->forge->createTable('auth_tokens', true);

        /*
         * Groups Table
         */
        $fields = [
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'varchar', 'constraint' => 255],
            'description' => ['type' => 'varchar', 'constraint' => 255],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('auth_groups', true);

        /*
         * Permissions Table
         */
        $fields = [
            'id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'varchar', 'constraint' => 255],
            'description' => ['type' => 'varchar', 'constraint' => 255],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable('auth_permissions', true);

        /*
         * Groups/Permissions Table
         */
        $fields = [
            'group_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'permission_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey(['group_id', 'permission_id']);
        $this->forge->addForeignKey('group_id', 'auth_groups', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'auth_permissions', 'id', false, 'CASCADE');
        $this->forge->createTable('auth_groups_permissions', true);

        /*
         * Users/Groups Table
         */
        $fields = [
            'group_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'user_id'  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey(['group_id', 'user_id']);
        $this->forge->addForeignKey('group_id', 'auth_groups', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
        $this->forge->createTable('auth_groups_users', true);

        /*
         * Users/Permissions Table
         */
        $fields = [
            'user_id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'permission_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ];

        $this->forge->addField($fields);
        $this->forge->addKey(['user_id', 'permission_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'auth_permissions', 'id', false, 'CASCADE');
        $this->forge->createTable('auth_users_permissions');
    }

    //--------------------------------------------------------------------

    public function down()
    {
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('auth_logins', true);
        $this->forge->dropTable('auth_tokens', true);
        $this->forge->dropTable('auth_groups', true);
        $this->forge->dropTable('auth_permissions', true);
        $this->forge->dropTable('auth_groups_permissions', true);
        $this->forge->dropTable('auth_groups_users', true);
        $this->forge->dropTable('auth_users_permissions', true);
    }
}
