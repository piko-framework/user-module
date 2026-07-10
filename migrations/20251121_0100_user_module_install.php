<?php

return new class
{
    public function up(PDO $db): void
    {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $file = realpath(__DIR__ . '/../sql/install-' . $driver . '.sql');

        if ($file === false || !is_readable($file)) {
            throw new Exception("Error: Could not find or read SQL installation file for driver '{$driver}'.\n");
        }

        $sql = file_get_contents($file);

        if ($db->exec($sql) === false) {
            $error = $db->errorInfo();
            throw new Exception("Database error [{$error[0]}]: {$error[2]}\n");
        }
    }

    public function down(PDO $db): void
    {
        $sql = '
            DROP TABLE IF EXISTS auth_assignment;
            DROP TABLE IF EXISTS auth_role_has_permission;
            DROP TABLE IF EXISTS auth_permission;
            DROP TABLE IF EXISTS auth_role;
            DROP TABLE IF EXISTS user;
        ';

        if ($db->exec($sql) === false) {
            $error = $db->errorInfo();
            throw new Exception("Database error [{$error[0]}]: {$error[2]}\n");
        }
    }
};
