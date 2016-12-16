<?php
namespace Iekadou\Quickies;


class DBConnector {

    const _cn = "Iekadou\\Quickies\\DBConnector";

    private $db_connection = null;

    public function __construct() {
        include(PATH."config/secrets.php");
        $this->db_connection = new \mysqli($secrets['db_host'], $secrets['db_user'], $secrets['db_pass'], $secrets['db_name']);
        unset($secrets);
    }

    public function query($sql_statement) {
        global $SQL_TIMES;
        $start = microtime(true);
        if (DB_DEBUG) {
            echo '<br>QUERY: '.$sql_statement.'<br>';
        }
        $result = $this->db_connection->query($sql_statement);
        if (DB_DEBUG) {
            $end = microtime(true);
            if (($end-$start) > 0.1) {
                echo "---->";
            }
            echo 'TOOK: '.($end-$start).' ms<br>';
            $SQL_TIMES += ($end-$start);
        }

        return $result;
    }

    public function multi_query($sql_statement) {
        global $SQL_TIMES;
        $start = microtime(true);
        if (DB_DEBUG) {
            echo 'Multi-QUERY: '.$sql_statement.'<br>';
        }
        $result = $this->db_connection->multi_query($sql_statement);
        if (DB_DEBUG) {
            $end = microtime(true);
            if (($end-$start) > 0.1) {
                echo "---->";
            }
            echo 'TOOK: '.($end-$start).' ms<br>';
            $SQL_TIMES += ($end-$start);
        }
        $results = array();
        do {
            array_push($results, $this->db_connection->use_result());
        } while( $this->db_connection->more_results() && $this->db_connection->next_result() );
        return $results;
    }

    public function get_connect_errno() {
        return $this->db_connection->connect_errno;
    }

    public function real_escape_string($string) {
        return $this->db_connection->real_escape_string($string);
    }

    public function autocommit($value) {
        return $this->db_connection->autocommit($value);
    }

    public function commit() {
        return $this->db_connection->commit();
    }

    public function get_error() {
        return $this->db_connection->error;
    }

    public function rollback() {
        return $this->db_connection->rollback();
    }

    public function get_insert_id() {
        return $this->db_connection->insert_id;
    }
}
