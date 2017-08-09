<?php
namespace Iekadou\Quickies;

class Migration {
    const _cn = "Iekadou\\Quickies\\Migration";

    private static $db_connection = null;
    private static $errors = array();
    private static $migrations_done = array();

    public static function init() {
        self::$db_connection = _i(DBConnector::_cn);
        if (!self::$db_connection->get_connect_errno()) {
            self::$errors[] = "Database connection problem.";
        }
        self::get_migrations_done();
        self::migrate();
    }

    private static function find_migrations() {
        $path = PATH."migrations/";
        $migrations = array();
        $files = array();
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (!empty($file) && !Utils::startsWith($file, '.') && is_file($path.$file)) {
                    if (file_exists($path.$file)) {
                        $files[] = $path.$file;
                    }
                }
            }
            sort($files);
            for($i=0; $i < sizeof($files); $i++) {
                include(PATH.'config/secrets.php');
                include($files[$i]);
                unset($secrets);
                if (isset($migration)) {
                    array_push($migrations, $migration);
                    unset($migration);
                }
            }
        }
        return $migrations;
    }

    public static function migrate() {
        include(PATH.'config/secrets.php');
        $migrations = self::find_migrations();
        $migrations_ok = true;
        $migration_count = count($migrations);
        $migrations_done_count = 0;
        self::$db_connection->autocommit(false);
        for ($i = 0; $i < $migration_count; $i++) {
            if (is_array($migrations[$i]) && array_key_exists('id', $migrations[$i]) && array_key_exists('query', $migrations[$i])) {
                if (!self::migration_already_done($migrations[$i]['id'])) {
                    print "running migration: ".$migrations[$i]['id']."\n";
                    if (self::$db_connection->multi_query($migrations[$i]['query'])) {
                        self::$db_connection->query("INSERT INTO `".$secrets['db_name']."`.`migrations` (`migration_id` ,`created_at`) VALUES ('" . $migrations[$i]['id'] . "',  '" . time() . "');");
                    } else {
                        $migrations_ok=false;
                    }
                    $migrations_done_count ++;
                }
            } else {
                echo "A migration is not correctly defined!<br>";
            }
        }
        if ($migrations_ok) {
            if (!self::$db_connection->commit()) {
                print 'Error: ' . self::$db_connection->get_error()."\n";
                self::$db_connection->rollback();
            } else {
                print "All ok! Migrations done: " . $migrations_done_count."\n";
            }
        } else {
            print 'Query-Error: ' . self::$db_connection->get_error()."\n";
            self::$db_connection->rollback();
        }
        self::$db_connection->autocommit(true);
        unset($secrets);
    }

    private static function get_migrations_done() {

        self::$db_connection->query("CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `migration_id` varchar(24) NOT NULL,
  `created_at` int(14) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

        if ($result = self::$db_connection->query("SELECT migration_id FROM migrations;")) {
            while($migration = $result->fetch_object()){
                array_push(self::$migrations_done, $migration->migration_id);
            }
        } else {
            echo "Migration-DB Error: ".self::$db_connection->get_error();
            exit();
        }
    }

    private static function migration_already_done($migration_id) {
        return in_array($migration_id, self::$migrations_done);
    }
}
