<?php

/**
 * PHP MYSQLi Database Class
 *
 * This class helps developers make standardized calls across their entire 
 * application. This class was found and forked (bennettstone/simple-mysqli)
 * as a necessity after mysql_connect was deprecated and my site crashed. I 
 * was forced to go through each and every page of my site and edit each call
 * and thought there had to be a better way. Please feel free to use on your 
 * site and submit issues where necessary. Thank you and happy coding.
 *
 *
 * @link              https://github.com/nowendwell/mysqli-class
 * @version           1.0.0
 *
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Last Update:       2016-12-13
 * Author:            Ben Miller
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 */


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'YOUR_DB_NAME' );

/** MySQL database username */
define( 'DB_USER', 'YOUR_DB_USER' );

/** MySQL database password */
define( 'DB_PASSWORD', 'YOUR_DB_PASS' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );


// ** Debug settings ** //
/** Should we send errors? */
define( 'SEND_EMAIL', false );

/** Who should receive any errors? */
define( 'SEND_ERRORS_TO', 'you@example.com' );

/** Show errors on screen? */
define( 'DISPLAY_DEBUG', true );

/** Log all queries */
define( 'SAVE_QUERIES_TO_LOG', false );


class DB
{
    private $link = null;
    public $filter;
    static $inst = null;
    public static $counter = 0;


    public function __construct()
    {
        mb_internal_encoding( 'UTF-8' );
        mb_regex_encoding( 'UTF-8' );

        $args = func_get_args();

        try {
            if (sizeof( $args ) > 0)
            {
            	$this->link = new mysqli( $args[0], $args[1], $args[2], $args[3] );
            } else {
            	$this->link = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
            }
        } catch ( Exception $e)
        {
            $this->log_db_errors( "Connect failed", $this->link->connect_error );
            die('Unable to connect to the database.');
        }

        if( $this->link->connect_errno )
        {
            $this->log_db_errors( "Connect failed", $this->link->connect_error );
            exit;
        }

        $this->link->set_charset( "utf8" );
    }

    public function __destruct()
    {
        if ( $this->link )
        {
            $this->disconnect();
        }
    }

    private function log_queries( $query )
    {
        if (SAVE_QUERIES_TO_LOG === true)
        {
        	$file = 'queries.log';
    		$string = "[" . date("m/d/y h:i:s A") . "]" . "\t$query\n";
    		file_put_contents( $file, $string, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Show the definition of a Procedure
     *
     * @access public
     * @param string (Name of the procedure)
     * @return array
     */
    public function show_procedure( $procedure )
    {

        if ( empty( $procedure ) )
        {
            return false;
        }

        $this->log_queries( $procedure );

        self::$counter++;
        //Overwrite the $row var to null
        $row = null;

        $results = $this->link->query( 'SHOW CREATE PROCEDURE ' . $procedure );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $procedure );
            return false;
        }
        else
        {
            $row = array();
            while( $r = ( !$object ) ? $results->fetch_assoc() : $results->fetch_object() )
            {
                $row[] = $r;
            }
            return $row;
        }
    }


    /**
     * Show the definition of a Function
     *
     * @access public
     * @param string (Name of the function)
     * @return array
     */
    public function show_function( $function )
    {
        if ( empty( $function ) )
        {
            return false;
        }

        $this->log_queries( $function );

        self::$counter++;
        //Overwrite the $row var to null
        $row = null;

        $results = $this->link->query( 'SHOW CREATE FUNCTION ' . $function );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $function );
            return false;
        }
        else
        {
            $row = array();
            while( $r = ( !$object ) ? $results->fetch_assoc() : $results->fetch_object() )
            {
                $row[] = $r;
            }
            return $row;
        }
    }

    /**
     * Allow the class to send admins a message alerting them to errors
     * on production sites
     *
     * @access public
     * @param string $error
     * @param string $query
     * @return mixed
     */
    public function log_db_errors( $error, $query )
    {
        $message = '<p>Error at '. date('Y-m-d H:i:s').':</p>';
        $message .= '<p>Query: '. htmlentities( $query ).'<br />';
        $message .= 'Error: ' . $error."<br />";
        $message .= "Page: " . $_SERVER["REQUEST_URI"]."<br />";
        $message .= "User: ". $_SESSION["name"];
        $message .= '</p>';

        if( defined( 'SEND_ERRORS_TO' ) )
        {
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'To: Admin <'.SEND_ERRORS_TO.'>' . "\r\n";
            $headers .= 'From: Agent Portal <gus@portal-amzwebcenter.com>' . "\r\n";

            if ( defined('SEND_EMAIL' ) && SEND_EMAIL === true)
            {
            	mail( SEND_ERRORS_TO, 'Database Error', $message, $headers);
            }
        }

        if( DISPLAY_DEBUG )
        {
            echo $message;
        }
    }



    /**
     * Sanitize user data
     *
     * Example usage:
     * $user_name = $database->filter( $_POST['user_name'] );
     *
     * Or to filter an entire array:
     * $data = array( 'name' => $_POST['name'], 'email' => 'email@address.com' );
     * $data = $database->filter( $data );
     *
     * @access public
     * @param mixed $data
     * @return mixed $data
     */
     public function filter( $data )
     {
         if( !is_array( $data ) )
         {
             $data = $this->link->real_escape_string( $data );
             $data = trim( htmlentities( $data, ENT_QUOTES, 'UTF-8', false ) );
         }
         else
         {
             //Self call function to sanitize array data
             $data = array_map( array( $this, 'filter' ), $data );
         }
         return $data;
     }


     /**
      * Extra function to filter when only mysqli_real_escape_string is needed
      * @access public
      * @param mixed $data
      * @return mixed $data
      */
     public function escape( $data )
     {
         if( !is_array( $data ) )
         {
             $data = $this->link->real_escape_string( $data );
         }
         else
         {
             //Self call function to sanitize array data
             $data = array_map( array( $this, 'escape' ), $data );
         }
         return $data;
     }


    /**
     * Normalize sanitized data for display (reverse $database->filter cleaning)
     *
     * Example usage:
     * echo $database->clean( $data_from_database );
     *
     * @access public
     * @param string $data
     * @return string $data
     */
     public function clean( $data )
     {
         $data = stripslashes( $data );
         $data = html_entity_decode( $data, ENT_QUOTES, 'UTF-8' );
         $data = nl2br( $data );
         $data = urldecode( $data );
         return $data;
     }


    /**
     * Determine if common non-encapsulated fields are being used
     *
     * Example usage:
     * if( $database->db_common( $query ) )
     * {
     *      //Do something
     * }
     * Used by function exists
     *
     * @access public
     * @param string
     * @param array
     * @return bool
     *
     */
    public function db_common( $value = '' )
    {
        if( is_array( $value ) )
        {
            foreach( $value as $v )
            {
                if( preg_match( '/AES_DECRYPT/i', $v ) || preg_match( '/AES_ENCRYPT/i', $v ) || preg_match( '/now()/i', $v ) || preg_match( '/NOW()/i', $v ) )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            if( preg_match( '/AES_DECRYPT/i', $value ) || preg_match( '/AES_ENCRYPT/i', $value ) || preg_match( '/now()/i', $value ) || preg_match( '/NOW()/i', $value ) )
            {
                return true;
            }
        }
    }


    /**
     * Perform queries
     * All following functions run through this function
     *
     * @access public
     * @param string
     * @return string
     * @return array
     * @return bool
     *
     */
    public function query( $query )
    {
    	$this->log_queries( $query );

        $full_query = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            return true;
        }
    }


    /**
     * Determine if database table exists
     * Example usage:
     * if( !$database->table_exists( 'checkingfortable' ) )
     * {
     *      //Install your table or throw error
     * }
     *
     * @access public
     * @param string
     * @return bool
     *
     */
     public function table_exists( $table )
     {
         self::$counter++;
         $check = $this->link->query( "SELECT 1 FROM $table" );
         if( $check !== false )
         {
             if( $check->num_rows > 0 )
             {
                 return true;
             }
             else
             {
                 return false;
             }
         }
         else
         {
             return false;
         }
     }


    /**
     * Count number of rows found matching a specific query
     *
     * Example usage:
     * $rows = $database->num_rows( "SELECT id FROM users WHERE user_id = 44" );
     *
     * @access public
     * @param string
     * @return int
     *
     */
    public function num_rows( $query )
    {
        self::$counter++;
        $num_rows = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $query );
            return $this->link->error;
        }
        else
        {
            return $num_rows->num_rows;
        }
    }


    /**
     * Run check to see if value exists, returns true or false
     *
     * Example Usage:
     * $check_user = array(
     *    'user_email' => 'someuser@gmail.com',
     *    'user_id' => 48
     * );
     * $exists = $database->exists( 'your_table', 'user_id', $check_user );
     *
     * @access public
     * @param string database table name
     * @param string field to check (i.e. 'user_id' or COUNT(user_id))
     * @param array column name => column value to match
     * @return bool
     *
     */
    public function exists( $table = '', $check_val = '', $params = array() )
    {
        self::$counter++;
        if( empty( $table ) || empty( $check_val ) || empty( $params ) )
        {
            return false;
        }
        $check = array();
        foreach( $params as $field => $value )
        {
            if( !empty( $field ) && !empty( $value ) )
            {
                //Check for frequently used mysql commands and prevent encapsulation of them
                if( $this->db_common( $value ) )
                {
                    $check[] = "$field = $value";
                }
                else
                {
                    $check[] = "$field = '$value'";
                }
            }

        }
        $check = implode(' AND ', $check);

        $rs_check = "SELECT $check_val FROM ".$table." WHERE $check";
        $number = $this->num_rows( $rs_check );
        if( $number === 0 )
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Return specific row based on db query
     *
     * Example usage:
     * list( $name, $email ) = $database->get_array( "SELECT name, email FROM users WHERE user_id = 44" );
     *
     * @access public
     * @param string
     * @param bool $object (true returns results as objects)
     * @return array
     *
     */
    public function get_array( $query, $type = MYSQLI_ASSOC )
    {
    	$this->log_queries( $query );
        self::$counter++;
        $row = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        }
        else
        {
        	while( $q = $row->fetch_array( $type ) )
            {
            	$r[] = $q;
            }
            return $r;
        }
    }

    /**
     * Return specific row based on db query
     *
     * Example usage:
     * list( $name, $email ) = $database->get_row( "SELECT name, email FROM users WHERE user_id = 44" );
     *
     * @access public
     * @param string
     * @param bool $object (true returns results as objects)
     * @return array
     *
     */
    public function get_row( $query, $object = false )
    {
    	$this->log_queries( $query );
        self::$counter++;
        $row = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            $r = ( !$object ) ? $row->fetch_assoc() : $row->fetch_object();
            return $r;
        }
    }

    /**
     * Perform query to retrieve single result
     *
     * Example usage:
     * echo $database->get_result( "SELECT name, email FROM users ORDER BY name ASC" );
     *
     * @access public
     * @param string
     * @param int|string    (Can be either position in the array or the name of the returned field)
     * @return string
     *
     */
    public function get_result( $query, $pos = null )
    {
    	$this->log_queries( $query );

        self::$counter++;
        //Overwrite the $row var to null
        $row = null;

        $results = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            $row = array();
            $result = $results->fetch_array();

            if ( $pos != null)
            {
            	return $result[0];
            } else {
            	return $result[$pos];
            }
        }
    }


    /**
     * Perform query to retrieve array of associated results
     *
     * Example usage:
     * $users = $database->get_results( "SELECT name, email FROM users ORDER BY name ASC" );
     * foreach( $users as $user )
     * {
     *      echo $user['name'] . ': '. $user['email'] .'<br />';
     * }
     *
     * @access public
     * @param string
     * @param bool $object (true returns object)
     * @return array
     *
     */
    public function get_results( $query, $object = false )
    {
    	$this->log_queries( $query );

        self::$counter++;
        //Overwrite the $row var to null
        $row = null;

        $results = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            $row = array();
            while( $r = ( !$object ) ? $results->fetch_assoc() : $results->fetch_object() )
            {
                $row[] = $r;
            }
            return $row;
        }
    }


    /**
     * Insert data into database table
     *
     * Example usage:
     * $user_data = array(
     *      'name' => 'Bennett',
     *      'email' => 'email@address.com',
     *      'active' => 1
     * );
     * $database->insert( 'users_table', $user_data );
     *
     * @access public
     * @param string table name
     * @param array table column => column value
     * @return bool
     *
     */
    public function insert( $table, $variables = array() )
    {
    	$this->log_queries( $query );

        self::$counter++;
        //Make sure the array isn't empty
        if( empty( $variables ) )
        {
            return false;
        }

        $sql = "INSERT INTO ". $table;
        $fields = array();
        $values = array();
        foreach( $variables as $field => $value )
        {
            $fields[] = $field;
            if ($value === NULL){
                $values[] = "NULL";
            } else {
                $values[] = "'".$value."'";
            }
        }
        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';

        $sql .= $fields .' VALUES '. $values;

        $query = $this->link->query( $sql );

        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $sql );
            return false;
        }
        else
        {
            return true;
        }
    }


    /**
     * Insert multiple records in a single query into a database table
     *
     * Example usage:
     * $fields = array(
     *      'name',
     *      'email',
     *      'active'
     *  );
     *  $records = array(
     *     array(
     *          'Bennett', 'bennett@email.com', 1
     *      ),
     *      array(
     *          'Lori', 'lori@email.com', 0
     *      ),
     *      array(
     *          'Nick', 'nick@nick.com', 1, 'This will not be added'
     *      ),
     *      array(
     *          'Meghan', 'meghan@email.com', 1
     *      )
     * );
     *  $database->insert_multi( 'users_table', $fields, $records );
     *
     * @access public
     * @param string table name
     * @param array table columns
     * @param nested array records
     * @return bool
     * @return int number of records inserted
     *
     */
    public function insert_multi( $table, $columns = array(), $records = array() )
    {
    	$this->log_queries( $query );

        self::$counter++;
        //Make sure the arrays aren't empty
        if( empty( $columns ) || empty( $records ) )
        {
            return false;
        }

        //Count the number of fields to ensure insertion statements do not exceed the same num
        $number_columns = count( $columns );

        //Start a counter for the rows
        $added = 0;

        //Start the query
        $sql = "INSERT INTO ". $table;

        $fields = array();
        //Loop through the columns for insertion preparation
        foreach( $columns as $field )
        {
            $fields[] = '`'.$field.'`';
        }
        $fields = ' (' . implode(', ', $fields) . ')';

        //Loop through the records to insert
        $values = array();
        foreach( $records as $record )
        {
            //Only add a record if the values match the number of columns
            if( count( $record ) == $number_columns )
            {
                $values[] = '(\''. implode( '\', \'', array_values( $record ) ) .'\')';
                $added++;
            }
        }
        $values = implode( ', ', $values );

        $sql .= $fields .' VALUES '. $values;

        $query = $this->link->query( $sql );

        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $sql );
            return false;
        }
        else
        {
            return $added;
        }
    }


    /**
     * Update data in database table
     *
     * Example usage:
     * $update = array( 'name' => 'Not bennett', 'email' => 'someotheremail@email.com' );
     * $update_where = array( 'user_id' => 44, 'name' => 'Bennett' );
     * $database->update( 'users_table', $update, $update_where, 1 );
     *
     * @access public
     * @param string table name
     * @param array values to update table column => column value
     * @param array where parameters table column => column value
     * @param int limit
     * @return bool
     *
     */
    public function update( $table, $variables = array(), $where = array(), $limit = '' )
    {
    	$this->log_queries( $query );

        self::$counter++;

        if( empty( $variables ) )
        {
            return false;
        }

        $sql = "UPDATE ". $table ." SET ";
        foreach( $variables as $field => $value )
        {
            if ($value === NULL)
            {
                $updates[] = "`$field` = NULL";
            } else {
                $updates[] = "`$field` = '$value'";
            }
        }
        $sql .= implode(', ', $updates);

        //Add the $where clauses as needed
        if( !empty( $where ) )
        {
            foreach( $where as $field => $value )
            {
                $value = $value;

                $clause[] = "$field = '$value'";
            }
            $sql .= ' WHERE '. implode(' AND ', $clause);
        }

        if( !empty( $limit ) )
        {
            $sql .= ' LIMIT '. $limit;
        }

        $query = $this->link->query( $sql );

        if( $this->link->error )
        {
            $this->log_db_errors( $this->link->error, $sql );
            return false;
        }
        else
        {
            return true;
        }
    }


    /**
     * Delete data from table
     *
     * Example usage:
     * $where = array( 'user_id' => 44, 'email' => 'someotheremail@email.com' );
     * $database->delete( 'users_table', $where, 1 );
     *
     * @access public
     * @param string table name
     * @param array where parameters table column => column value
     * @param int max number of rows to remove.
     * @return bool
     *
     */
    public function delete( $table, $where = array(), $limit = '' )
    {
    	$this->log_queries( $query );

        self::$counter++;
        //Delete clauses require a where param, otherwise use "truncate"
        if( empty( $where ) )
        {
            return false;
        }

        $sql = "DELETE FROM ". $table;
        foreach( $where as $field => $value )
        {
            $value = $value;
            $clause[] = "$field = '$value'";
        }
        $sql .= " WHERE ". implode(' AND ', $clause);

        if( !empty( $limit ) )
        {
            $sql .= " LIMIT ". $limit;
        }

        $query = $this->link->query( $sql );

        if( $this->link->error )
        {
            //return false; //
            $this->log_db_errors( $this->link->error, $sql );
            return false;
        }
        else
        {
            return true;
        }
    }


    /**
     * Get last auto-incrementing ID associated with an insertion
     *
     * Example usage:
     * $database->insert( 'users_table', $user );
     * $last = $database->lastid();
     *
     * @access public
     * @param none
     * @return int
     *
     */
    public function lastid()
    {
        self::$counter++;
        return $this->link->insert_id;
    }


    /**
     * Return the number of rows affected by a given query
     *
     * Example usage:
     * $database->insert( 'users_table', $user );
     * $database->affected();
     *
     * @access public
     * @param none
     * @return int
     */
    public function affected()
    {
        return $this->link->affected_rows;
    }


    /**
     * Get number of fields
     *
     * Example usage:
     * echo $database->num_fields( "SELECT * FROM users_table" );
     *
     * @access public
     * @param query
     * @return int
     */
    public function num_fields( $query )
    {
        self::$counter++;
        $query = $this->link->query( $query );
        $fields = $query->field_count;
        return $fields;
    }


    /**
     * Get field names associated with a table
     *
     * Example usage:
     * $fields = $database->list_fields( "SELECT * FROM users_table" );
     * echo '<pre>';
     * print_r( $fields );
     * echo '</pre>';
     *
     * @access public
     * @param query
     * @return array
     */
    public function list_fields( $query )
    {
        self::$counter++;
        $query = $this->link->query( $query );
        $listed_fields = $query->fetch_fields();
        return $listed_fields;
    }


    /**
     * Truncate entire tables
     *
     * Example usage:
     * $remove_tables = array( 'users_table', 'user_data' );
     * echo $database->truncate( $remove_tables );
     *
     * @access public
     * @param array database table names
     * @return int number of tables truncated
     *
     */
    public function truncate( $tables = array() )
    {
        if( !empty( $tables ) )
        {
            $truncated = 0;
            foreach( $tables as $table )
            {
                $truncate = "TRUNCATE TABLE `".trim( $table )."`";
                $this->link->query( $truncate );
                if( !$this->link->error )
                {
                    $truncated++;
                    self::$counter++;
                }
            }
            return $truncated;
        }
    }

    /**
     * Optimize tables
     *
     * Example usage:
     * $tables = array( 'users_table', 'user_data' );
     * echo $database->optimize( $tables );
     *
     * @access public
     * @param array database table names
     * @return int number of tables truncated
     *
     */
    public function optimize( $tables = array() )
    {
        if( !empty( $tables ) )
        {
            $optimized = 0;
            foreach( $tables as $table )
            {
                $optimize = "OPTIMIZE TABLE `".trim( $table )."`";
                $this->link->query( $optimize );
                if( !$this->link->error )
                {
                    $optimized++;
                    self::$counter++;
                }
            }
            return $optimized;
        }
    }


    /**
     * Output the total number of queries
     * Generally designed to be used at the bottom of a page after
     * scripts have been run and initialized as needed
     *
     * Example usage:
     * echo 'There were '. $database->total_queries() . ' performed';
     *
     * @access public
     * @param none
     * @return int
     */
    public function total_queries()
    {
        return self::$counter;
    }


    /**
     * Singleton function
     *
     * Example usage:
     * $database = DB::getInstance();
     *
     * @access private
     * @return self
     */
    static function get_instance()
    {
        if( self::$inst == null )
        {
            self::$inst = new DB();
        }
        return self::$inst;
    }


    /**
     * Disconnect from db server
     * Called automatically from __destruct function
     */
    public function disconnect()
    {
        $this->link->close();
    }

} //end class DB
