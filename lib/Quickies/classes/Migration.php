<?php
namespace Iekadou\Quickies;

class Migration {
    const _cn = "Iekadou\\Quickies\\Migration";

    private $db_connection = null;
    private $migrations_done = array();

    public function __construct() {
        $this->db_connection = _i(DBConnector::_cn);
        if (!$this->db_connection->get_connect_errno()) {
            $this->errors[] = "Database connection problem.";
        }
        $this->get_migrations_done();
        $this->migrate();
    }

    private function find_migrations() {
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

    public function migrate() {
        include(PATH.'config/secrets.php');
        $migrations = $this->find_migrations();
        $migrations_ok = true;
        $migration_count = count($migrations);
        $migrations_done_count = 0;
        $this->db_connection->autocommit(false);
        for ($i = 0; $i < $migration_count; $i++) {
            if (is_array($migrations[$i]) && array_key_exists('id', $migrations[$i]) && array_key_exists('query', $migrations[$i])) {
                if (!$this->migration_already_done($migrations[$i]['id'])) {
                    echo "doing migration: ".$migrations[$i]['id']."<br>";
                    if ($this->db_connection->multi_query($migrations[$i]['query'])) {
                        $this->db_connection->query("INSERT INTO `".$secrets['db_name']."`.`migrations` (`migration_id` ,`created_at`) VALUES ('" . $migrations[$i]['id'] . "',  '" . time() . "');");
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
            if (!$this->db_connection->commit()) {
                echo 'Error: ' . $this->db_connection->get_error().'<br>';
                $this->db_connection->rollback();
            } else {
                echo "All ok! Migrations done: " . $migrations_done_count . "<br>";
            }
        } else {
            echo 'Query-Error: ' . $this->db_connection->get_error().'<br>';
            $this->db_connection->rollback();
        }
        $this->db_connection->autocommit(true);
        unset($secrets);
    }

    private function get_migrations_done() {

        $this->db_connection->query("CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `migration_id` varchar(24) NOT NULL,
  `created_at` int(14) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

        if ($result = $this->db_connection->query("SELECT migration_id FROM migrations;")) {
            while($migration = $result->fetch_object()){
                array_push($this->migrations_done, $migration->migration_id);
            }
        } else {
            echo "Migration-DB Error: ".$this->db_connection->get_error();
            exit();
        }
    }

    private function migration_already_done($migration_id) {
        return in_array($migration_id, $this->migrations_done);
    }
}