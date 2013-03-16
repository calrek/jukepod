<?php
class db {
	function __construct( $db_server = "", $db_user = "", $db_passwd = "", $db = "", $create_db = 0 ) {
		if ( !$db_server )
			$db_server = DB_HOST;
		if ( !$db_user )
			$db_user = DB_USER;
		if ( !$db_passwd )
			$db_passwd = DB_PASSWORD;
		if ( !$db )
			$db = DB_NAME;

		$this->db_server = $db_server;
		$this->db_user = $db_user;
		$this->db_passwd = $db_passwd;
		$this->db = $db;

		$this->connect();
		if ( $create_db )
			$this->create_db();
		$this->select_db();

		$this->output = "screen";
		$this->crlf = "\n";
		$this->tab = "\t";

		$this->output_switch["query"] = 0;
		$this->output_switch["warning"] = 1;
		$this->output_switch["error"] = 1;

		$this->sql_array = array();
	}

	function __destruct() {}

	function create_db() {
		$query = "CREATE DATABASE ".$this->db;
		mysql_query( $query, $this->conn_id )or $this->error();
	}

	function connect() {
		$this->conn_id = mysql_connect( $this->db_server, $this->db_user, $this->db_passwd )or $this->error();
	}

	function select_db() {
		mysql_select_db( $this->db )or die( "Could not select database" );
	}

	function query( $query ) {
		global $formgen;

		$this->output( $query, "query" );

		$this->last_query = $query;

		$result = mysql_query( $query, $this->conn_id )or $this->error();

		return $result;
	}

	function fetch_array( $result ) {
		if ( $result )
			return mysql_fetch_array( $result );
		else
			return false;
	}

	function num_rows( $result ) {
		if ( $result )
			return mysql_num_rows( $result );
		else
			return false;
	}

	function result( $result, $row, $field ) {
		if ( $result and mysql_num_rows( $result ) )
			return mysql_result( $result, $row, $field );
		else
			return false;
	}

	function insert_id() {
		return mysql_insert_id( $this->conn_id );
	}

	function affected_rows() {
		return mysql_affected_rows( $this->conn_id );
	}

	function field_len( $result, $col ) {
		if ( $result )
			return mysql_field_len( $result, $col );
		else
			return false;
	}

	function field_type( $result, $col ) {
		if ( $result )
			return mysql_field_type( $result, $col );
		else
			return false;
	}

	function list_tables( $db ) {
		return mysql_list_tables( $db );
	}

	function tablename( $result, $i ) {
		if ( $result )
			return mysql_tablename( $result, $i );
		else
			return false;
	}

	function error() {
		$this->output( $this->last_query.": ".mysql_error(), "error" );
	}

	function fetch_query_array( $query ) {
		$result = $this->query( $query );
		if ( $this->num_rows( $result ) ) {
			while ( $row = $this->fetch_array( $result ) ) {
				$array[] = $row;
			}
			return $array;
		} else {
			return 0;
		}
	}

	function output( $string, $type ) {
		if ( $this->output_switch[$type] ) {
			$_SESSION["sql_log"][] = array( $string, $type );
		}
	}
}

if ( !defined( "SCRIPT" )or SCRIPT != "install_db" )
	$db = new db();
?>
