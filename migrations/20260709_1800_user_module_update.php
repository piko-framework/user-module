<?php

return new class
{
    public function up(PDO $db): void
    {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver !== 'mysql') {
            return;
        }

        $this->runMigration($db, true);
    }

    public function down(PDO $db): void
    {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver !== 'mysql') {
            return;
        }

        $this->runMigration($db, false);
    }

    private function runMigration(PDO $db, bool $toUnsigned): void
    {
        $idType = $toUnsigned ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT' : 'INT NOT NULL AUTO_INCREMENT';
        $fkType = $toUnsigned ? 'INT UNSIGNED NOT NULL' : 'INT NOT NULL';

        $dropForeignKeys = [
            'ALTER TABLE `auth_assignment` DROP FOREIGN KEY `auth_assignment_ibfk_1`',
            'ALTER TABLE `auth_assignment` DROP FOREIGN KEY `auth_assignment_ibfk_2`',
            'ALTER TABLE `auth_role_has_permission` DROP FOREIGN KEY `auth_role_has_permission_ibfk_1`',
            'ALTER TABLE `auth_role_has_permission` DROP FOREIGN KEY `auth_role_has_permission_ibfk_2`',
        ];

        $alterColumns = [
            "ALTER TABLE `user` MODIFY COLUMN `id` {$idType}",
            "ALTER TABLE `auth_role` MODIFY COLUMN `id` {$idType}",
            "ALTER TABLE `auth_permission` MODIFY COLUMN `id` {$idType}",
            "ALTER TABLE `auth_role_has_permission` MODIFY COLUMN `role_id` {$fkType}",
            "ALTER TABLE `auth_role_has_permission` MODIFY COLUMN `permission_id` {$fkType}",
            "ALTER TABLE `auth_assignment` MODIFY COLUMN `role_id` {$fkType}",
            "ALTER TABLE `auth_assignment` MODIFY COLUMN `user_id` {$fkType}",
        ];

        $addForeignKeys = [
            'ALTER TABLE `auth_role_has_permission` ADD CONSTRAINT `auth_role_has_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            'ALTER TABLE `auth_role_has_permission` ADD CONSTRAINT `auth_role_has_permission_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            'ALTER TABLE `auth_assignment` ADD CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            'ALTER TABLE `auth_assignment` ADD CONSTRAINT `auth_assignment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
        ];

        $db->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach (array_merge($dropForeignKeys, $alterColumns, $addForeignKeys) as $sql) {
                if ($db->exec($sql) === false) {
                    $error = $db->errorInfo();
                    throw new Exception("Database error [{$error[0]}]: {$error[2]}");
                }
            }
        } finally {
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
};
