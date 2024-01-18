<?php

namespace RedBeanPHP {

/**
 * RedBean Logging interface.
 * Provides a uniform and convenient logging
 * interface throughout RedBeanPHP.
 *
 * @file    RedBean/Logging.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface Logger
{
	/**
	 * A logger (for PDO or OCI driver) needs to implement the log method.
	 * The log method will receive logging data. Note that the number of parameters is 0, this means
	 * all parameters are optional and the number may vary. This way the logger can be used in a very
	 * flexible way. Sometimes the logger is used to log a simple error message and in other
	 * situations sql and bindings are passed.
	 * The log method should be able to accept all kinds of parameters and data by using
	 * functions like func_num_args/func_get_args.
	 *
	 * @param string $message, ...
	 *
	 * @return void
	 */
	public function log();
}
}

namespace RedBeanPHP\Logger {

use RedBeanPHP\Logger as Logger;
use RedBeanPHP\RedException as RedException;

/**
 * Logger. Provides a basic logging function for RedBeanPHP.
 *
 * @file    RedBeanPHP/Logger.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RDefault implements Logger
{
	/**
	 * Logger modes
	 */
	const C_LOGGER_ECHO  = 0;
	const C_LOGGER_ARRAY = 1;

	/**
	 * @var integer
	 */
	protected $mode = 0;

	/**
	 * @var array
	 */
	protected $logs = array();

	/**
	 * Default logger method logging to STDOUT.
	 * This is the default/reference implementation of a logger.
	 * This method will write the message value to STDOUT (screen) unless
	 * you have changed the mode of operation to C_LOGGER_ARRAY.
	 *
	 * @param $message (optional) message to log (might also be data or output)
	 *
	 * @return void
	 */
	public function log()
	{
		if ( func_num_args() < 1 ) return;

		foreach ( func_get_args() as $argument ) {
			if ( is_array( $argument ) ) {
				$log = var_export( $argument, TRUE );
				if ( $this->mode === self::C_LOGGER_ECHO ) {
					echo $log;
				} else {
					$this->logs[] = $log;
				}
			} else {
				if ( $this->mode === self::C_LOGGER_ECHO ) {
					echo $argument;
				} else {
					$this->logs[] = $argument;
				}
			}

			if ( $this->mode === self::C_LOGGER_ECHO ) echo "<br>" . PHP_EOL;
		}
	}

	/**
	 * Returns the internal log array.
	 * The internal log array is where all log messages are stored.
	 *
	 * @return array
	 */
	public function getLogs()
	{
		return $this->logs;
	}

	/**
	 * Clears the internal log array, removing all
	 * previously stored entries.
	 *
	 * @return self
	 */
	public function clear()
	{
		$this->logs = array();
		return $this;
	}

	/**
	 * Selects a logging mode.
	 * There are several options available.
	 *
	 * * C_LOGGER_ARRAY - log silently, stores entries in internal log array only
	 * * C_LOGGER_ECHO  - also forward log messages directly to STDOUT
	 *
	 * @param integer $mode mode of operation for logging object
	 *
	 * @return self
	 */
	public function setMode( $mode )
	{
		if ($mode !== self::C_LOGGER_ARRAY && $mode !== self::C_LOGGER_ECHO ) {
			throw new RedException( 'Invalid mode selected for logger, use C_LOGGER_ARRAY or C_LOGGER_ECHO.' );
		}
		$this->mode = $mode;
		return $this;
	}

	/**
	 * Searches for all log entries in internal log array
	 * for $needle and returns those entries.
	 * This method will return an array containing all matches for your
	 * search query.
	 *
	 * @param string $needle phrase to look for in internal log array
	 *
	 * @return array
	 */
	public function grep( $needle )
	{
		$found = array();
		foreach( $this->logs as $logEntry ) {
			if ( strpos( $logEntry, $needle ) !== FALSE ) $found[] = $logEntry;
		}
		return $found;
	}
}
}

namespace RedBeanPHP\Logger\RDefault {

use RedBeanPHP\Logger as Logger;
use RedBeanPHP\Logger\RDefault as RDefault;
use RedBeanPHP\RedException as RedException;

/**
 * Debug logger.
 * A special logger for debugging purposes.
 * Provides debugging logging functions for RedBeanPHP.
 *
 * @file    RedBeanPHP/Logger/RDefault/Debug.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class Debug extends RDefault implements Logger
{
	/**
	 * @var integer
	 */
	protected $strLen = 40;

	/**
	 * @var boolean
	 */
	protected static $noCLI = FALSE;

	/**
	 * @var boolean
	 */
	protected $flagUseStringOnlyBinding = FALSE;

	/**
	 * Toggles CLI override. By default debugging functions will
	 * output differently based on PHP_SAPI values. This function
	 * allows you to override the PHP_SAPI setting. If you set
	 * this to TRUE, CLI output will be suppressed in favour of
	 * HTML output. So, to get HTML on the command line use
	 * setOverrideCLIOutput( TRUE ).
	 *
	 * @param boolean $yesNo CLI-override setting flag
	 *
	 * @return void
	 */
	public static function setOverrideCLIOutput( $yesNo )
	{
		self::$noCLI = $yesNo;
	}

	/**
	 * Writes a query for logging with all bindings / params filled
	 * in.
	 *
	 * @param string $newSql      the query
	 * @param array  $newBindings the bindings to process (key-value pairs)
	 *
	 * @return string
	 */
	protected function writeQuery( $newSql, $newBindings )
	{
		//avoid str_replace collisions: slot1 and slot10 (issue 407).
		uksort( $newBindings, function( $a, $b ) {
			return ( strlen( $b ) - strlen( $a ) );
		} );

		$newStr = $newSql;
		foreach( $newBindings as $slot => $value ) {
			if ( strpos( $slot, ':' ) === 0 ) {
				$newStr = str_replace( $slot, $this->fillInValue( $value ), $newStr );
			}
		}
		return $newStr;
	}

	/**
	 * Fills in a value of a binding and truncates the
	 * resulting string if necessary.
	 *
	 * @param mixed $value bound value
	 *
	 * @return string
	 */
	protected function fillInValue( $value )
	{
		if ( is_array( $value ) && count( $value ) == 2 ) {
			$paramType = end( $value );
			$value = reset( $value );
		} else {
			$paramType = NULL;
		}

		if ( is_null( $value ) ) $value = 'NULL';

		if ( $this->flagUseStringOnlyBinding ) $paramType = \PDO::PARAM_STR;

		if ( $paramType != \PDO::PARAM_INT && $paramType != \PDO::PARAM_STR ) {
			if ( \RedBeanPHP\QueryWriter\AQueryWriter::canBeTreatedAsInt( $value ) || $value === 'NULL') {
				$paramType = \PDO::PARAM_INT;
			} else {
				$paramType = \PDO::PARAM_STR;
			}
		}

		if ( strlen( $value ) > ( $this->strLen ) ) {
			$value = substr( $value, 0, ( $this->strLen ) ).'... ';
		}

		if ($paramType === \PDO::PARAM_STR) {
			$value = '\''.$value.'\'';
		}

		return $value;
	}

	/**
	 * Depending on the current mode of operation,
	 * this method will either log and output to STDIN or
	 * just log.
	 *
	 * Depending on the value of constant PHP_SAPI this function
	 * will format output for console or HTML.
	 *
	 * @param string $str string to log or output and log
	 *
	 * @return void
	 */
	protected function output( $str )
	{
		$this->logs[] = $str;
		if ( !$this->mode ) {
			$highlight = FALSE;
			/* just a quick heuristic to highlight schema changes */
			if ( strpos( $str, 'CREATE' ) === 0
			|| strpos( $str, 'ALTER' ) === 0
			|| strpos( $str, 'DROP' ) === 0) {
				$highlight = TRUE;
			}
			if (PHP_SAPI === 'cli' && !self::$noCLI) {
				if ($highlight) echo "\e[91m";
				echo $str, PHP_EOL;
				echo "\e[39m";
			} else {
				if ($highlight) {
					echo "<b style=\"color:red\">{$str}</b>";
				} else {
					echo $str;
				}
				echo '<br />';
			}
		}
	}

	/**
	 * Normalizes the slots in an SQL string.
	 * Replaces question mark slots with :slot1 :slot2 etc.
	 *
	 * @param string $sql sql to normalize
	 *
	 * @return string
	 */
	protected function normalizeSlots( $sql )
	{
		$newSql = $sql;
		$i = 0;
		while(strpos($newSql, '?') !== FALSE ){
			$pos   = strpos( $newSql, '?' );
			$slot  = ':slot'.$i;
			$begin = substr( $newSql, 0, $pos );
			$end   = substr( $newSql, $pos+1 );
			if (PHP_SAPI === 'cli' && !self::$noCLI) {
				$newSql = "{$begin}\e[32m{$slot}\e[39m{$end}";
			} else {
				$newSql = "{$begin}<b style=\"color:green\">$slot</b>{$end}";
			}
			$i ++;
		}
		return $newSql;
	}

	/**
	 * Normalizes the bindings.
	 * Replaces numeric binding keys with :slot1 :slot2 etc.
	 *
	 * @param array $bindings bindings to normalize
	 *
	 * @return array
	 */
	protected function normalizeBindings( $bindings )
	{
		$i = 0;
		$newBindings = array();
		foreach( $bindings as $key => $value ) {
			if ( is_numeric($key) ) {
				$newKey = ':slot'.$i;
				$newBindings[$newKey] = $value;
				$i++;
			} else {
				$newBindings[$key] = $value;
			}
		}
		return $newBindings;
	}

	/**
	 * Logger method.
	 *
	 * Takes a number of arguments tries to create
	 * a proper debug log based on the available data.
	 *
	 * @return void
	 */
	public function log()
	{
		if ( func_num_args() < 1 ) return;

		$sql = func_get_arg( 0 );

		if ( func_num_args() < 2) {
			$bindings = array();
		} else {
			$bindings = func_get_arg( 1 );
		}

		if ( !is_array( $bindings ) ) {
			return $this->output( $sql );
		}

		$newSql = $this->normalizeSlots( $sql );
		$newBindings = $this->normalizeBindings( $bindings );
		$newStr = $this->writeQuery( $newSql, $newBindings );
		$this->output( $newStr );
	}

	/**
	 * Sets the max string length for the parameter output in
	 * SQL queries. Set this value to a reasonable number to
	 * keep you SQL queries readable.
	 *
	 * @param integer $len string length
	 *
	 * @return self
	 */
	public function setParamStringLength( $len = 20 )
	{
		$this->strLen = max(0, $len);
		return $this;
	}

	/**
	 * Whether to bind all parameters as strings.
	 * If set to TRUE this will cause all integers to be bound as STRINGS.
	 * This will NOT affect NULL values.
	 *
	 * @param boolean $yesNo pass TRUE to bind all parameters as strings.
	 *
	 * @return self
	 */
	public function setUseStringOnlyBinding( $yesNo = false )
	{
		$this->flagUseStringOnlyBinding = (boolean) $yesNo;
		return $this;
	}
}
}

namespace RedBeanPHP {

/**
 * Interface for database drivers.
 * The Driver API conforms to the ADODB pseudo standard
 * for database drivers.
 *
 * @file       RedBeanPHP/Driver.php
 * @author     Gabor de Mooij and the RedBeanPHP Community
 * @license    BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface Driver
{
	/**
	 * Runs a query and fetches results as a multi dimensional array.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return array
	 */
	public function GetAll( $sql, $bindings = array() );

	/**
	 * Runs a query and fetches results as a column.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return array
	 */
	public function GetCol( $sql, $bindings = array() );

	/**
	 * Runs a query and returns results as a single cell.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return mixed
	 */
	public function GetOne( $sql, $bindings = array() );

	/**
	 * Runs a query and returns results as an associative array
	 * indexed by the first column.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return array
	 */
	public function GetAssocRow( $sql, $bindings = array() );

	/**
	 * Runs a query and returns a flat array containing the values of
	 * one row.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return array
	 */
	public function GetRow( $sql, $bindings = array() );

	/**
	 * Executes SQL code and allows key-value binding.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL. This method has no return value.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return int Affected Rows
	 */
	public function Execute( $sql, $bindings = array() );

	/**
	 * Returns the latest insert ID if driver does support this
	 * feature.
	 *
	 * @return integer
	 */
	public function GetInsertID();

	/**
	 * Returns the number of rows affected by the most recent query
	 * if the currently selected driver driver supports this feature.
	 *
	 * @return integer
	 */
	public function Affected_Rows();

	/**
	 * Returns a cursor-like object from the database.
	 *
	 * @param string $sql      SQL query to execute
	 * @param array  $bindings list of values to bind to SQL snippet
	 *
	 * @return Cursor
	 */
	public function GetCursor( $sql, $bindings = array() );

	/**
	 * Toggles debug mode. In debug mode the driver will print all
	 * SQL to the screen together with some information about the
	 * results.
	 *
	 * This method is for more fine-grained control. Normally
	 * you should use the facade to start the query debugger for
	 * you. The facade will manage the object wirings necessary
	 * to use the debugging functionality.
	 *
	 * Usage (through facade):
	 *
	 * <code>
	 * R::debug( TRUE );
	 * ...rest of program...
	 * R::debug( FALSE );
	 * </code>
	 *
	 * The example above illustrates how to use the RedBeanPHP
	 * query debugger through the facade.
	 *
	 * @param boolean $trueFalse turn on/off
	 * @param Logger  $logger    logger instance
	 *
	 * @return void
	 */
	public function setDebugMode( $tf, $customLogger );

	/**
	 * Commits a transaction.
	 *
	 * @return void
	 */
	public function CommitTrans();

	/**
	 * Starts a transaction.
	 *
	 * @return void
	 */
	public function StartTrans();

	/**
	 * Rolls back a transaction.
	 *
	 * @return void
	 */
	public function FailTrans();

	/**
	 * Resets the internal Query Counter.
	 *
	 * @return self
	 */
	public function resetCounter();

	/**
	 * Returns the number of SQL queries processed.
	 *
	 * @return integer
	 */
	public function getQueryCount();

	/**
	 * Sets initialization code for connection.
	 *
	 * @param callable|NULL $code code
	 *
	 * @return void
	 */
	public function setInitCode( $code );

	/**
	 * Returns the version string from the database server.
	 *
	 * @return string
	 */
	public function DatabaseServerVersion();
}
}

namespace RedBeanPHP\Driver {

use RedBeanPHP\Driver as Driver;
use RedBeanPHP\Logger as Logger;
use RedBeanPHP\QueryWriter\AQueryWriter as AQueryWriter;
use RedBeanPHP\RedException as RedException;
use RedBeanPHP\RedException\SQL as SQL;
use RedBeanPHP\Logger\RDefault as RDefault;
use RedBeanPHP\PDOCompatible as PDOCompatible;
use RedBeanPHP\Cursor\PDOCursor as PDOCursor;

/**
 * PDO Driver
 * This Driver implements the RedBean Driver API.
 * for RedBeanPHP. This is the standard / default database driver
 * for RedBeanPHP.
 *
 * @file    RedBeanPHP/PDO.php
 * @author  Gabor de Mooij and the RedBeanPHP Community, Desfrenes
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) Desfrenes & Gabor de Mooij and the RedBeanPHP community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RPDO implements Driver
{
	/**
	 * @var integer
	 */
	protected $max;

	/**
	 * @var string
	 */
	protected $dsn;

	/**
	 * @var boolean
	 */
	protected $loggingEnabled = FALSE;

	/**
	 * @var Logger|NULL
	 */
	protected $logger = NULL;

	/**
	 * @var \PDO|NULL
	 */
	protected $pdo;

	/**
	 * @var integer
	 */
	protected $affectedRows;

	/**
	 * @var array
	 */
	protected $resultArray;

	/**
	 * @var array
	 */
	protected $connectInfo = array();

	/**
	 * @var boolean
	 */
	protected $isConnected = FALSE;

	/**
	 * @var bool
	 */
	protected $flagUseStringOnlyBinding = FALSE;

	/**
	 * @var integer
	 */
	protected $queryCounter = 0;

	/**
	 * @var string
	 */
	protected $mysqlCharset = '';

	/**
	 * @var string
	 */
	protected $mysqlCollate = '';

	/**
	 * @var boolean
	 */
	protected $stringifyFetches = TRUE;

	/**
	 * @var string|NULL
	 */
	protected $initSQL = NULL;

	/**
	 * @var callable|NULL
	 */
	protected $initCode = NULL;

	/**
	 * Binds parameters. This method binds parameters to a PDOStatement for
	 * Query Execution. This method binds parameters as NULL, INTEGER or STRING
	 * and supports both named keys and question mark keys.
	 *
	 * @param \PDOStatement $statement PDO Statement instance
	 * @param array         $bindings  values that need to get bound to the statement
	 *
	 * @return void
	 */
	protected function bindParams( $statement, $bindings )
	{
		foreach ( $bindings as $key => &$value ) {
			$k = is_integer( $key ) ? $key + 1 : $key;

			if ( is_array( $value ) && count( $value ) == 2 ) {
				$paramType = end( $value );
				$value = reset( $value );
			} else {
				$paramType = NULL;
			}

			if ( is_null( $value ) ) {
				$statement->bindValue( $k, NULL, \PDO::PARAM_NULL );
				continue;
			}

			if ( $paramType != \PDO::PARAM_INT && $paramType != \PDO::PARAM_STR ) {
				if ( !$this->flagUseStringOnlyBinding && AQueryWriter::canBeTreatedAsInt( $value ) && abs( $value ) <= $this->max ) {
					$paramType = \PDO::PARAM_INT;
				} else {
					$paramType = \PDO::PARAM_STR;
				}
			}

			$statement->bindParam( $k, $value, $paramType );
		}
	}

	/**
	 * This method runs the actual SQL query and binds a list of parameters to the query.
	 * slots. The result of the query will be stored in the protected property
	 * $rs (always array). The number of rows affected (result of rowcount, if supported by database)
	 * is stored in protected property $affectedRows. If the debug flag is set
	 * this function will send debugging output to screen buffer.
	 *
	 * @param string $sql      the SQL string to be send to database server
	 * @param array  $bindings the values that need to get bound to the query slots
	 * @param array  $options
	 *
	 * @return mixed
	 * @throws SQL
	 */
	public function runQuery( $sql, $bindings, $options = array() )
	{
		$this->connect();
		if ( $this->loggingEnabled && $this->logger ) {
			$this->logger->log( $sql, $bindings );
		}
		try {
			if ( strpos( 'pgsql', $this->dsn ) === 0 ) {
				if (defined('\\PDO::PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT')) {
                 			$statement = @$this->pdo->prepare($sql, array(\PDO::PGSQL_ATTR_DISABLE_NATIVE_PREPARED_STATEMENT => TRUE));
             			} else {
                 			$statement = $this->pdo->prepare($sql);
             			}
			} else {
				$statement = $this->pdo->prepare( $sql );
			}
			$this->bindParams( $statement, $bindings );
			$statement->execute();
			$this->queryCounter ++;
			$this->affectedRows = $statement->rowCount();
			if ( isset( $options['noFetch'] ) && $options['noFetch'] ) {
				$this->resultArray = array();
				return $statement;
			}
			if ( $statement->columnCount() ) {
				$fetchStyle = ( isset( $options['fetchStyle'] ) ) ? $options['fetchStyle'] : NULL;
				if ( is_null( $fetchStyle) ) {
					$this->resultArray = $statement->fetchAll();
				} else {
					$this->resultArray = $statement->fetchAll( $fetchStyle );
				}
				if ( $this->loggingEnabled && $this->logger ) {
					$this->logger->log( 'resultset: ' . count( $this->resultArray ) . ' rows' );
				}
			} else {
				$this->resultArray = array();
			}
		} catch ( \PDOException $e ) {
			//Unfortunately the code field is supposed to be int by default (php)
			//So we need a property to convey the SQL State code.
			$err = $e->getMessage();
			if ( $this->loggingEnabled && $this->logger ) $this->logger->log( 'An error occurred: ' . $err );
			$exception = new SQL( $err, 0, $e );
			$exception->setSQLState( $e->getCode() );
			$exception->setDriverDetails( $e->errorInfo );
			throw $exception;
		}
	}

	/**
	 * Try to fix MySQL character encoding problems.
	 * MySQL < 5.5.3 does not support proper 4 byte unicode but they
	 * seem to have added it with version 5.5.3 under a different label: utf8mb4.
	 * We try to select the best possible charset based on your version data.
	 *
	 * @return void
	 */
	protected function setEncoding()
	{
		$driver = $this->pdo->getAttribute( \PDO::ATTR_DRIVER_NAME );
		if ($driver === 'mysql') {
			$charset = $this->hasCap( 'utf8mb4' ) ? 'utf8mb4' : 'utf8';
			$collate = $this->hasCap( 'utf8mb4_520' ) ? '_unicode_520_ci' : '_unicode_ci';
			$this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES '. $charset ); //on every re-connect
			/* #624 removed space before SET NAMES because it causes trouble with ProxySQL */
			$this->pdo->exec('SET NAMES '. $charset); //also for current connection
			$this->mysqlCharset = $charset;
			$this->mysqlCollate = $charset . $collate;
		}
	}

	/**
	 * Determine if a database supports a particular feature.
	 * Currently this function can be used to detect the following features:
	 *
	 * - utf8mb4
	 * - utf8mb4 520
	 *
	 * Usage:
	 *
	 * <code>
	 * $this->hasCap( 'utf8mb4_520' );
	 * </code>
	 *
	 * By default, RedBeanPHP uses this method under the hood to make sure
	 * you use the latest UTF8 encoding possible for your database.
	 *
	 * @param string $db_cap identifier of database capability
	 *
	 * @return int|false Whether the database feature is supported, FALSE otherwise.
	 **/
	protected function hasCap( $db_cap )
	{
		$compare = FALSE;
		$version = $this->pdo->getAttribute( \PDO::ATTR_SERVER_VERSION );
		switch ( strtolower( $db_cap ) ) {
			case 'utf8mb4':
				//oneliner, to boost code coverage (coverage does not span versions)
				if ( version_compare( $version, '5.5.3', '<' ) ) { return FALSE; }
				$client_version = $this->pdo->getAttribute(\PDO::ATTR_CLIENT_VERSION );
				/*
				 * libmysql has supported utf8mb4 since 5.5.3, same as the MySQL server.
				 * mysqlnd has supported utf8mb4 since 5.0.9.
				 */
				if ( strpos( $client_version, 'mysqlnd' ) !== FALSE ) {
					$client_version = preg_replace( '/^\D+([\d.]+).*/', '$1', $client_version );
					$compare = version_compare( $client_version, '5.0.9', '>=' );
				} else {
					$compare = version_compare( $client_version, '5.5.3', '>=' );
				}
			break;
			case 'utf8mb4_520':
				$compare = version_compare( $version, '5.6', '>=' );
			break;
		}

		return $compare;
	}

	/**
	 * Constructor. You may either specify dsn, user and password or
	 * just give an existing PDO connection.
	 *
	 * Usage:
	 *
	 * <code>
	 * $driver = new RPDO( $dsn, $user, $password );
	 * </code>
	 *
	 * The example above illustrates how to create a driver
	 * instance from a database connection string (dsn), a username
	 * and a password. It's also possible to pass a PDO object.
	 *
	 * Usage:
	 *
	 * <code>
	 * $driver = new RPDO( $existingConnection );
	 * </code>
	 *
	 * The second example shows how to create an RPDO instance
	 * from an existing PDO object.
	 *
	 * @param string|\PDO   $dsn  database connection string
	 * @param string        $user optional, username to sign in
	 * @param string        $pass optional, password for connection login
	 *
	 * @return void
	 */
	public function __construct( $dsn, $user = NULL, $pass = NULL, $options = array() )
	{
		if ( is_object( $dsn ) ) {
			$this->pdo = $dsn;
			$this->isConnected = TRUE;
			$this->setEncoding();
			$this->pdo->setAttribute( \PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION );
			$this->pdo->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE,\PDO::FETCH_ASSOC );
			// make sure that the dsn at least contains the type
			$this->dsn = $this->getDatabaseType();
		} else {
			$this->dsn = $dsn;
			$this->connectInfo = array( 'pass' => $pass, 'user' => $user );
			if (is_array($options)) $this->connectInfo['options'] = $options;
		}

		//PHP 5.3 PDO SQLite has a bug with large numbers:
		if ( ( strpos( $this->dsn, 'sqlite' ) === 0 && PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION === 3 ) ||  defined('HHVM_VERSION') || $this->dsn === 'test-sqlite-53' ) {
			$this->max = 2147483647; //otherwise you get -2147483648 ?! demonstrated in build #603 on Travis.
		} elseif ( strpos( $this->dsn, 'cubrid' ) === 0 ) {
			$this->max = 2147483647; //bindParam in pdo_cubrid also fails...
		} else {
			$this->max = PHP_INT_MAX; //the normal value of course (makes it possible to use large numbers in LIMIT clause)
		}
	}

	/**
	 * Sets PDO in stringify fetch mode.
	 * If set to TRUE, this method will make sure all data retrieved from
	 * the database will be fetched as a string. Default: TRUE.
	 *
	 * To set it to FALSE...
	 *
	 * Usage:
	 *
	 * <code>
	 * R::getDatabaseAdapter()->getDatabase()->stringifyFetches( FALSE );
	 * </code>
	 *
	 * Important!
	 * Note, this method only works if you set the value BEFORE the connection
	 * has been establish. Also, this setting ONLY works with SOME drivers.
	 * It's up to the driver to honour this setting.
	 *
	 * @param boolean $bool
	 */
	public function stringifyFetches( $bool ) {
		$this->stringifyFetches = $bool;
	}

	/**
	 * Returns the best possible encoding for MySQL based on version data.
	 * This method can be used to obtain the best character set parameters
	 * possible for your database when constructing a table creation query
	 * containing clauses like:  CHARSET=... COLLATE=...
	 * This is a MySQL-specific method and not part of the driver interface.
	 *
	 * Usage:
	 *
	 * <code>
	 * $charset_collate = $this->adapter->getDatabase()->getMysqlEncoding( TRUE );
	 * </code>
	 *
	 * @param boolean $retCol pass TRUE to return both charset/collate
	 *
	 * @return string|array
	 */
	public function getMysqlEncoding( $retCol = FALSE )
	{
		if( $retCol )
			return array( 'charset' => $this->mysqlCharset, 'collate' => $this->mysqlCollate );
		return $this->mysqlCharset;
	}

	/**
	 * Whether to bind all parameters as strings.
	 * If set to TRUE this will cause all integers to be bound as STRINGS.
	 * This will NOT affect NULL values.
	 *
	 * @param boolean $yesNo pass TRUE to bind all parameters as strings.
	 *
	 * @return void
	 */
	public function setUseStringOnlyBinding( $yesNo )
	{
		$this->flagUseStringOnlyBinding = (boolean) $yesNo;
		if ( $this->loggingEnabled && $this->logger && method_exists($this->logger,'setUseStringOnlyBinding')) {
			$this->logger->setUseStringOnlyBinding( $this->flagUseStringOnlyBinding );
		}
	}

	/**
	 * Sets the maximum value to be bound as integer, normally
	 * this value equals PHP's MAX INT constant, however sometimes
	 * PDO driver bindings cannot bind large integers as integers.
	 * This method allows you to manually set the max integer binding
	 * value to manage portability/compatibility issues among different
	 * PHP builds. This method will return the old value.
	 *
	 * @param integer $max maximum value for integer bindings
	 *
	 * @return integer
	 */
	public function setMaxIntBind( $max )
	{
		if ( !is_integer( $max ) ) throw new RedException( 'Parameter has to be integer.' );
		$oldMax = $this->max;
		$this->max = $max;
		return $oldMax;
	}

	/**
	 * Sets initialization code to execute upon connecting.
	 *
	 * @param callable|NULL $code
	 *
	 * @return void
	 */
	public function setInitCode($code)
	{
		$this->initCode= $code;
	}

	/**
	 * Establishes a connection to the database using PHP\PDO
	 * functionality. If a connection has already been established this
	 * method will simply return directly. This method also turns on
	 * UTF8 for the database and PDO-ERRMODE-EXCEPTION as well as
	 * PDO-FETCH-ASSOC.
	 *
	 * @return void
	 */
	public function connect()
	{
		if ( $this->isConnected ) return;
		try {
			$user = $this->connectInfo['user'];
			$pass = $this->connectInfo['pass'];
			$options = array();
			if (isset($this->connectInfo['options']) && is_array($this->connectInfo['options'])) {
				$options = $this->connectInfo['options'];
			}
			$this->pdo = new \PDO( $this->dsn, $user, $pass, $options );
			$this->setEncoding();
			$this->pdo->setAttribute( \PDO::ATTR_STRINGIFY_FETCHES, $this->stringifyFetches );
			//cant pass these as argument to constructor, CUBRID driver does not understand...
			$this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$this->pdo->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC );
			$this->isConnected = TRUE;
			/* run initialisation query if any */
			if ( $this->initSQL !== NULL ) {
				$this->Execute( $this->initSQL );
				$this->initSQL = NULL;
			}
			if ( $this->initCode !== NULL ) {
				$code = $this->initCode;
				$code( $this->pdo->getAttribute( \PDO::ATTR_SERVER_VERSION ) );
			}
		} catch ( \PDOException $exception ) {
			$matches = array();
			$dbname  = ( preg_match( '/dbname=(\w+)/', $this->dsn, $matches ) ) ? $matches[1] : '?';
			throw new \PDOException( 'Could not connect to database (' . $dbname . ').', $exception->getCode() );
		}
	}

	/**
	 * Directly sets PDO instance into driver.
	 * This method might improve performance, however since the driver does
	 * not configure this instance terrible things may happen... only use
	 * this method if you are an expert on RedBeanPHP, PDO and UTF8 connections and
	 * you know your database server VERY WELL.
	 *
	 * - connected     TRUE|FALSE (treat this instance as connected, default: TRUE)
	 * - setEncoding   TRUE|FALSE (let RedBeanPHP set encoding for you, default: TRUE)
	 * - setAttributes TRUE|FALSE (let RedBeanPHP set attributes for you, default: TRUE)*
	 * - setDSNString  TRUE|FALSE (extract DSN string from PDO instance, default: TRUE)
	 * - stringFetch   TRUE|FALSE (whether you want to stringify fetches or not, default: TRUE)
	 * - runInitCode   TRUE|FALSE (run init code if any, default: TRUE)
	 *
	 * *attributes:
	 * - RedBeanPHP will ask database driver to throw Exceptions on errors (recommended for compatibility)
         * - RedBeanPHP will ask database driver to use associative arrays when fetching (recommended for compatibility)
	 *
	 * @param \PDO    $pdo       PDO instance
	 * @param array   $options   Options to apply
	 *
	 * @return void
	 */
	public function setPDO( \PDO $pdo, $options = array() ) {
		$this->pdo = $pdo;

		$connected     = TRUE;
		$setEncoding   = TRUE;
		$setAttributes = TRUE;
		$setDSNString  = TRUE;
		$runInitCode   = TRUE;
		$stringFetch   = TRUE;

		if ( isset($options['connected']) )     $connected     = $options['connected'];
		if ( isset($options['setEncoding']) )   $setEncoding   = $options['setEncoding'];
		if ( isset($options['setAttributes']) ) $setAttributes = $options['setAttributes'];
		if ( isset($options['setDSNString']) )  $setDSNString  = $options['setDSNString'];
		if ( isset($options['runInitCode']) )   $runInitCode   = $options['runInitCode'];
		if ( isset($options['stringFetch']) )   $stringFetch   = $options['stringFetch'];

		if ($connected) $this->isConnected = $connected;
		if ($setEncoding) $this->setEncoding();
		if ($setAttributes) {
			$this->pdo->setAttribute( \PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION );
			$this->pdo->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE,\PDO::FETCH_ASSOC );
			$this->pdo->setAttribute( \PDO::ATTR_STRINGIFY_FETCHES, $stringFetch );
		}
		if ($runInitCode) {
			/* run initialisation query if any */
			if ( $this->initSQL !== NULL ) {
				$this->Execute( $this->initSQL );
				$this->initSQL = NULL;
			}
			if ( $this->initCode !== NULL ) {
				$code = $this->initCode;
				$code( $this->pdo->getAttribute( \PDO::ATTR_SERVER_VERSION ) );
			}
		}
		if ($setDSNString) $this->dsn = $this->getDatabaseType();
	}

	/**
	 * @see Driver::GetAll
	 */
	public function GetAll( $sql, $bindings = array() )
	{
		$this->runQuery( $sql, $bindings );
		return $this->resultArray;
	}

	/**
	 * @see Driver::GetAssocRow
	 */
	public function GetAssocRow( $sql, $bindings = array() )
	{
		$this->runQuery( $sql, $bindings, array(
				'fetchStyle' => \PDO::FETCH_ASSOC
			)
		);
		return $this->resultArray;
	}

	/**
	 * @see Driver::GetCol
	 */
	public function GetCol( $sql, $bindings = array() )
	{
		$rows = $this->GetAll( $sql, $bindings );

		if ( empty( $rows ) || !is_array( $rows ) ) {
			return array();
		}

		$cols = array();
		foreach ( $rows as $row ) {
			$cols[] = reset( $row );
		}

		return $cols;
	}

	/**
	 * @see Driver::GetOne
	 */
	public function GetOne( $sql, $bindings = array() )
	{
		$arr = $this->GetAll( $sql, $bindings );

		if ( empty( $arr[0] ) || !is_array( $arr[0] ) ) {
			return NULL;
		}

		return reset( $arr[0] );
	}

	/**
	 * Alias for getOne().
	 * Backward compatibility.
	 *
	 * @param string $sql      SQL
	 * @param array  $bindings bindings
	 *
	 * @return string|NULL
	 */
	public function GetCell( $sql, $bindings = array() )
	{
		return $this->GetOne( $sql, $bindings );
	}

	/**
	 * @see Driver::GetRow
	 */
	public function GetRow( $sql, $bindings = array() )
	{
		$arr = $this->GetAll( $sql, $bindings );

		if ( is_array( $arr ) && count( $arr ) ) {
			return reset( $arr );
		}

		return array();
	}

	/**
	 * @see Driver::Execute
	 */
	public function Execute( $sql, $bindings = array() )
	{
		$this->runQuery( $sql, $bindings );
		return $this->affectedRows;
	}

	/**
	 * @see Driver::GetInsertID
	 */
	public function GetInsertID()
	{
		$this->connect();

		return (int) $this->pdo->lastInsertId();
	}

	/**
	 * @see Driver::GetCursor
	 */
	public function GetCursor( $sql, $bindings = array() )
	{
		$statement = $this->runQuery( $sql, $bindings, array( 'noFetch' => TRUE ) );
		$cursor = new PDOCursor( $statement, \PDO::FETCH_ASSOC );
		return $cursor;
	}

	/**
	 * @see Driver::Affected_Rows
	 */
	public function Affected_Rows()
	{
		$this->connect();
		return (int) $this->affectedRows;
	}

	/**
	 * @see Driver::setDebugMode
	 */
	public function setDebugMode( $tf, $logger = NULL )
	{
		$this->connect();
		$this->loggingEnabled = (bool) $tf;
		if ( $this->loggingEnabled and !$logger ) {
			$logger = new RDefault();
		}
		$this->setLogger( $logger );
	}

	/**
	 * Injects Logger object.
	 * Sets the logger instance you wish to use.
	 *
	 * This method is for more fine-grained control. Normally
	 * you should use the facade to start the query debugger for
	 * you. The facade will manage the object wirings necessary
	 * to use the debugging functionality.
	 *
	 * Usage (through facade):
	 *
	 * <code>
	 * R::debug( TRUE );
	 * ...rest of program...
	 * R::debug( FALSE );
	 * </code>
	 *
	 * The example above illustrates how to use the RedBeanPHP
	 * query debugger through the facade.
	 *
	 * @param Logger $logger the logger instance to be used for logging
	 *
	 * @return self
	 */
	public function setLogger( Logger $logger )
	{
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Gets Logger object.
	 * Returns the currently active Logger instance.
	 *
	 * @return Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * @see Driver::StartTrans
	 */
	public function StartTrans()
	{
		$this->connect();
		$this->pdo->beginTransaction();
	}

	/**
	 * @see Driver::CommitTrans
	 */
	public function CommitTrans()
	{
		$this->connect();
		$this->pdo->commit();
	}

	/**
	 * @see Driver::FailTrans
	 */
	public function FailTrans()
	{
		$this->connect();
		$this->pdo->rollback();
	}

	/**
	 * Returns the name of database driver for PDO.
	 * Uses the PDO attribute DRIVER NAME to obtain the name of the
	 * PDO driver. Use this method to identify the current PDO driver
	 * used to provide access to the database. Example of a database
	 * driver string:
	 *
	 * <code>
	 * mysql
	 * </code>
	 *
	 * Usage:
	 *
	 * <code>
	 * echo R::getDatabaseAdapter()->getDatabase()->getDatabaseType();
	 * </code>
	 *
	 * The example above prints the current database driver string to
	 * stdout.
	 *
	 * Note that this is a driver-specific method, not part of the
	 * driver interface. This method might not be available in other
	 * drivers since it relies on PDO.
	 *
	 * @return string
	 */
	public function getDatabaseType()
	{
		$this->connect();
		return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME );
	}

	/**
	 * Returns the version identifier string of the database client.
	 * This method can be used to identify the currently installed
	 * database client. Note that this method will also establish a connection
	 * (because this is required to obtain the version information).
	 *
	 * Example of a version string:
	 *
	 * <code>
	 * mysqlnd 5.0.12-dev - 20150407 - $Id: b5c5906d452ec590732a93b051f3827e02749b83 $
	 * </code>
	 *
	 * Usage:
	 *
	 * <code>
	 * echo R::getDatabaseAdapter()->getDatabase()->getDatabaseVersion();
	 * </code>
	 *
	 * The example above will print the version string to stdout.
	 *
	 * Note that this is a driver-specific method, not part of the
	 * driver interface. This method might not be available in other
	 * drivers since it relies on PDO.
	 *
	 * To obtain the database server version, use getDatabaseServerVersion()
	 * instead.
	 *
	 * @return mixed
	 */
	public function getDatabaseVersion()
	{
		$this->connect();
		return $this->pdo->getAttribute(\PDO::ATTR_CLIENT_VERSION );
	}

	/**
	 * Returns the underlying PHP PDO instance.
	 * For some low-level database operations you'll need access to the PDO
	 * object. Not that this method is only available in RPDO and other
	 * PDO based database drivers for RedBeanPHP. Other drivers may not have
	 * a method like this. The following example demonstrates how to obtain
	 * a reference to the PDO instance from the facade:
	 *
	 * Usage:
	 *
	 * <code>
	 * $pdo = R::getDatabaseAdapter()->getDatabase()->getPDO();
	 * </code>
	 *
	 * @return \PDO
	 */
	public function getPDO()
	{
		$this->connect();
		return $this->pdo;
	}

	/**
	 * Closes the database connection.
	 * While database connections are closed automatically at the end of the PHP script,
	 * closing database connections is generally recommended to improve performance.
	 * Closing a database connection will immediately return the resources to PHP.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::setup( ... );
	 * ... do stuff ...
	 * R::close();
	 * </code>
	 *
	 * @return void
	 */
	public function close()
	{
		$this->pdo         = NULL;
		$this->isConnected = FALSE;
	}

	/**
	 * Returns TRUE if the current PDO instance is connected.
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		return $this->isConnected && $this->pdo;
	}

	/**
	 * Toggles logging, enables or disables logging.
	 *
	 * @param boolean $enable TRUE to enable logging
	 *
	 * @return self
	 */
	public function setEnableLogging( $enable )
	{
		$this->loggingEnabled = (boolean) $enable;
		return $this;
	}

	/**
	 * Resets the query counter.
	 * The query counter can be used to monitor the number
	 * of database queries that have
	 * been processed according to the database driver. You can use this
	 * to monitor the number of queries required to render a page.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::resetQueryCount();
	 * echo R::getQueryCount() . ' queries processed.';
	 * </code>
	 *
	 * @return self
	 */
	public function resetCounter()
	{
		$this->queryCounter = 0;
		return $this;
	}

	/**
	 * Returns the number of SQL queries processed.
	 * This method returns the number of database queries that have
	 * been processed according to the database driver. You can use this
	 * to monitor the number of queries required to render a page.
	 *
	 * Usage:
	 *
	 * <code>
	 * echo R::getQueryCount() . ' queries processed.';
	 * </code>
	 *
	 * @return integer
	 */
	public function getQueryCount()
	{
		return $this->queryCounter;
	}

	/**
	 * Returns the maximum value treated as integer parameter
	 * binding.
	 *
	 * This method is mainly for testing purposes but it can help
	 * you solve some issues relating to integer bindings.
	 *
	 * @return integer
	 */
	public function getIntegerBindingMax()
	{
		return $this->max;
	}

	/**
	 * Sets a query to be executed upon connecting to the database.
	 * This method provides an opportunity to configure the connection
	 * to a database through an SQL-based interface. Objects can provide
	 * an SQL string to be executed upon establishing a connection to
	 * the database. This has been used to solve issues with default
	 * foreign key settings in SQLite3 for instance, see Github issues:
	 * #545 and #548.
	 *
	 * @param string $sql SQL query to run upon connecting to database
	 *
	 * @return self
	 */
	public function setInitQuery( $sql ) {
		$this->initSQL = $sql;
		return $this;
	}

	/**
	 * Returns the version string from the database server.
	 *
	 * @return string
	 */
	public function DatabaseServerVersion() {
		return trim( strval( $this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION) ) );
	}
}
}

namespace RedBeanPHP {

use RedBeanPHP\QueryWriter\AQueryWriter as AQueryWriter;
use RedBeanPHP\BeanHelper as BeanHelper;
use RedBeanPHP\RedException as RedException;
use RedBeanPHP\Util\Either as Either;

/**
 * PHP 5.3 compatibility
 * We extend JsonSerializable to avoid namespace conflicts,
 * can't define interface with special namespace in PHP
 */
if (interface_exists('\JsonSerializable')) { interface Jsonable extends \JsonSerializable {}; } else { interface Jsonable {}; }

/**
 * OODBBean (Object Oriented DataBase Bean).
 *
 * to exchange information with the database. A bean represents
 * a single table row and offers generic services for interaction
 * with databases systems as well as some meta-data.
 *
 * @file    RedBeanPHP/OODBBean.php
 * @author  Gabor de Mooij and the RedBeanPHP community
 * @license BSD/GPLv2
 * @desc    OODBBean represents a bean. RedBeanPHP uses beans
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class OODBBean implements \IteratorAggregate,\ArrayAccess,\Countable,Jsonable
{
	/**
	 * FUSE error modes.
	 */
	const C_ERR_IGNORE    = FALSE;
	const C_ERR_LOG       = 1;
	const C_ERR_NOTICE    = 2;
	const C_ERR_WARN      = 3;
	const C_ERR_EXCEPTION = 4;
	const C_ERR_FUNC      = 5;
	const C_ERR_FATAL     = 6;

	/**
	 * @var boolean
	 */
	protected static $useFluidCount = FALSE;

	/**
	 * @var boolean
	 */
	protected static $convertArraysToJSON = FALSE;

	/**
	 * @var boolean
	 */
	protected static $errorHandlingFUSE = FALSE;

	/**
	 * @var callable|NULL
	 */
	protected static $errorHandler = NULL;

	/**
	 * @var array
	 */
	protected static $aliases = array();

	/**
	 * If this is set to TRUE, the __toString function will
	 * encode all properties as UTF-8 to repair invalid UTF-8
	 * encodings and prevent exceptions (which are uncatchable from within
	 * a __toString-function).
	 *
	 * @var boolean
	 */
	protected static $enforceUTF8encoding = FALSE;

	/**
	 * This is where the real properties of the bean live. They are stored and retrieved
	 * by the magic getter and setter (__get and __set).
	 *
	 * @var array $properties
	 */
	protected $properties = array();

	/**
	 * Here we keep the meta data of a bean.
	 *
	 * @var array
	 */
	protected $__info = array();

	/**
	 * The BeanHelper allows the bean to access the toolbox objects to implement
	 * rich functionality, otherwise you would have to do everything with R or
	 * external objects.
	 *
	 * @var BeanHelper|NULL
	 */
	protected $beanHelper = NULL;

	/**
	 * @var string|NULL
	 */
	protected $fetchType = NULL;

	/**
	 * @var string
	 */
	protected $withSql = '';

	/**
	 * @var array
	 */
	protected $withParams = array();

	/**
	 * @var string|NULL
	 */
	protected $aliasName = NULL;

	/**
	 * @var string|NULL
	 */
	protected $via = NULL;

	/**
	 * @var boolean
	 */
	protected $noLoad = FALSE;

	/**
	 * @var boolean
	 */
	protected $all = FALSE;

	/**
	 * @var string|NULL
	 */
	protected $castProperty = NULL;

	/**
	 * If fluid count is set to TRUE then $bean->ownCount() will
	 * return 0 if the table does not exists.
	 * Only for backward compatibility.
	 * Returns previous value.
	 *
	 * @param boolean $toggle toggle
	 *
	 * @return boolean
	 */
	public static function useFluidCount( $toggle )
	{
		$old = self::$useFluidCount;
		self::$useFluidCount = $toggle;
		return $old;
	}

	/**
	 * If this is set to TRUE, the __toString function will
	 * encode all properties as UTF-8 to repair invalid UTF-8
	 * encodings and prevent exceptions (which are uncatchable from within
	 * a __toString-function).
	 *
	 * @param boolean $toggle TRUE to enforce UTF-8 encoding (slower)
	 *
	 * @return void
	 */
	 public static function setEnforceUTF8encoding( $toggle )
	 {
		 self::$enforceUTF8encoding = (boolean) $toggle;
	 }

	/**
	 * Sets the error mode for FUSE.
	 * What to do if a FUSE model method does not exist?
	 * You can set the following options:
	 *
	 * * OODBBean::C_ERR_IGNORE (default), ignores the call, returns NULL
	 * * OODBBean::C_ERR_LOG, logs the incident using error_log
	 * * OODBBean::C_ERR_NOTICE, triggers a E_USER_NOTICE
	 * * OODBBean::C_ERR_WARN, triggers a E_USER_WARNING
	 * * OODBBean::C_ERR_EXCEPTION, throws an exception
	 * * OODBBean::C_ERR_FUNC, allows you to specify a custom handler (function)
	 * * OODBBean::C_ERR_FATAL, triggers a E_USER_ERROR
	 *
	 * <code>
	 * Custom handler method signature: handler( array (
	 * 	'message' => string
	 * 	'bean' => OODBBean
	 * 	'method' => string
	 * ) )
	 * </code>
	 *
	 * This method returns the old mode and handler as an array.
	 *
	 * @param integer       $mode error handling mode
	 * @param callable|NULL $func custom handler
	 *
	 * @return array
	 */
	public static function setErrorHandlingFUSE($mode, $func = NULL) {
		if (
			   $mode !== self::C_ERR_IGNORE
			&& $mode !== self::C_ERR_LOG
			&& $mode !== self::C_ERR_NOTICE
			&& $mode !== self::C_ERR_WARN
			&& $mode !== self::C_ERR_EXCEPTION
			&& $mode !== self::C_ERR_FUNC
			&& $mode !== self::C_ERR_FATAL
		) throw new \Exception( 'Invalid error mode selected' );

		if ( $mode === self::C_ERR_FUNC && !is_callable( $func ) ) {
			throw new \Exception( 'Invalid error handler' );
		}

		$old = array( self::$errorHandlingFUSE, self::$errorHandler );
		self::$errorHandlingFUSE = $mode;
		if ( is_callable( $func ) ) {
			self::$errorHandler = $func;
		} else {
			self::$errorHandler = NULL;
		}
		return $old;
	}

	/**
	 * Toggles array to JSON conversion. If set to TRUE any array
	 * set to a bean property that's not a list will be turned into
	 * a JSON string. Used together with AQueryWriter::useJSONColumns this
	 * extends the data type support for JSON columns. Returns the previous
	 * value of the flag.
	 *
	 * @param boolean $flag flag
	 *
	 * @return boolean
	 */
	public static function convertArraysToJSON( $flag )
	{
		$old = self::$convertArraysToJSON;
		self::$convertArraysToJSON = $flag;
		return $old;
	}

	/**
	 * Sets global aliases.
	 * Registers a batch of aliases in one go. This works the same as
	 * fetchAs and setAutoResolve but explicitly. For instance if you register
	 * the alias 'cover' for 'page' a property containing a reference to a
	 * page bean called 'cover' will correctly return the page bean and not
	 * a (non-existent) cover bean.
	 *
	 * <code>
	 * R::aliases( array( 'cover' => 'page' ) );
	 * $book = R::dispense( 'book' );
	 * $page = R::dispense( 'page' );
	 * $book->cover = $page;
	 * R::store( $book );
	 * $book = $book->fresh();
	 * $cover = $book->cover;
	 * echo $cover->getMeta( 'type' ); //page
	 * </code>
	 *
	 * The format of the aliases registration array is:
	 *
	 * {alias} => {actual type}
	 *
	 * In the example above we use:
	 *
	 * cover => page
	 *
	 * From that point on, every bean reference to a cover
	 * will return a 'page' bean. Note that with autoResolve this
	 * feature along with fetchAs() is no longer very important, although
	 * relying on explicit aliases can be a bit faster.
	 *
	 * @param array $list list of global aliases to use
	 *
	 * @return void
	 */
	public static function aliases( $list )
	{
		self::$aliases = $list;
	}

	/**
	 * Return list of global aliases
	 *
	 * @return array
	 */
	public static function getAliases()
	{
		return self::$aliases;
	}

	/**
	 * Sets a meta property for all beans. This is a quicker way to set
	 * the meta properties for a collection of beans because this method
	 * can directly access the property arrays of the beans.
	 * This method returns the beans.
	 *
	 * @param array  $beans    beans to set the meta property of
	 * @param string $property property to set
	 * @param mixed  $value    value
	 *
	 * @return array
	 */
	public static function setMetaAll( $beans, $property, $value )
	{
		foreach( $beans as $bean ) {
			if ( $bean instanceof OODBBean ) $bean->__info[ $property ] = $value;
			if ( $property == 'type' && !empty($bean->beanHelper)) {
				$bean->__info['model'] = $bean->beanHelper->getModelForBean( $bean );
			}
		}
		return $beans;
	}

	/**
	 * Accesses the shared list of a bean.
	 * To access beans that have been associated with the current bean
	 * using a many-to-many relationship use sharedXList where
	 * X is the type of beans in the list.
	 *
	 * Usage:
	 *
	 * <code>
	 * $person = R::load( 'person', $id );
	 * $friends = $person->sharedFriendList;
	 * </code>
	 *
	 * The code snippet above demonstrates how to obtain all beans of
	 * type 'friend' that have associated using an N-M relation.
	 * This is a private method used by the magic getter / accessor.
	 * The example illustrates usage through these accessors.
	 *
	 * @param string  $type    the name of the list you want to retrieve
	 * @param OODB    $redbean instance of the RedBeanPHP OODB class
	 * @param ToolBox $toolbox instance of ToolBox (to get access to core objects)
	 *
	 * @return array
	 */
	private function getSharedList( $type, $redbean, $toolbox )
	{
		$writer = $toolbox->getWriter();
		if ( $this->via ) {
			$oldName = $writer->getAssocTable( array( $this->__info['type'], $type ) );
			if ( $oldName !== $this->via ) {
				//set the new renaming rule
				$writer->renameAssocTable( $oldName, $this->via );
			}
			$this->via = NULL;
		}
		$beans = array();
		if ($this->getID()) {
			$type             = $this->beau( $type );
			$assocManager     = $redbean->getAssociationManager();
			$beans            = $assocManager->related( $this, $type, $this->withSql, $this->withParams );
		}
		return $beans;
	}

	/**
	 * Accesses the ownList. The 'own' list contains beans
	 * associated using a one-to-many relation. The own-lists can
	 * be accessed through the magic getter/setter property
	 * ownXList where X is the type of beans in that list.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book = R::load( 'book', $id );
	 * $pages = $book->ownPageList;
	 * </code>
	 *
	 * The example above demonstrates how to access the
	 * pages associated with the book. Since this is a private method
	 * meant to be used by the magic accessors, the example uses the
	 * magic getter instead.
	 *
	 * @param string      $type   name of the list you want to retrieve
	 * @param OODB        $oodb   The RB OODB object database instance
	 *
	 * @return array
	 */
	private function getOwnList( $type, $redbean )
	{
		$type = $this->beau( $type );
		if ( $this->aliasName ) {
			$parentField = $this->aliasName;
			$myFieldLink = $parentField . '_id';

			$this->__info['sys.alias.' . $type] = $this->aliasName;

			$this->aliasName = NULL;
		} else {
			$parentField = $this->__info['type'];
			$myFieldLink = $parentField . '_id';
		}
		$beans = array();
		if ( $this->getID() ) {
			reset( $this->withParams );
			$firstKey = count( $this->withParams ) > 0
				? key( $this->withParams )
				: 0;
			if ( is_int( $firstKey ) ) {
				$sql = "{$myFieldLink} = ? {$this->withSql}";
				$bindings = array_merge( array( $this->getID() ), $this->withParams );
			} else {
				$sql = "{$myFieldLink} = :slot0 {$this->withSql}";
				$bindings           = $this->withParams;
				$bindings[':slot0'] = $this->getID();
			}
			$beans = $redbean->find( $type, array(), $sql, $bindings );
		}
		foreach ( $beans as $beanFromList ) {
			$beanFromList->__info['sys.parentcache.' . $parentField] = $this;
		}
		return $beans;
	}

	/**
	 * Initializes a bean. Used by OODB for dispensing beans.
	 * It is not recommended to use this method to initialize beans. Instead
	 * use the OODB object to dispense new beans. You can use this method
	 * if you build your own bean dispensing mechanism.
	 * This is not recommended.
	 *
	 * Unless you know what you are doing, do NOT use this method.
	 * This is for advanced users only!
	 *
	 * @param string          $type       type of the new bean
	 * @param BeanHelper|NULL $beanhelper bean helper to obtain a toolbox and a model
	 *
	 * @return void
	 */
	public function initializeForDispense( $type, $beanhelper = NULL )
	{
		$this->beanHelper         = $beanhelper;
		$this->__info['type']     = $type;
		$this->__info['sys.id']   = 'id';
		$this->__info['sys.orig'] = array( 'id' => 0 );
		$this->__info['tainted']  = TRUE;
		$this->__info['changed']  = TRUE;
		$this->__info['changelist'] = array();
		if ( $beanhelper ) {
			$this->__info['model'] = $this->beanHelper->getModelForBean( $this );
		}
		$this->properties['id']   = 0;
	}

	/**
	 * Sets the Bean Helper. Normally the Bean Helper is set by OODB.
	 * Here you can change the Bean Helper. The Bean Helper is an object
	 * providing access to a toolbox for the bean necessary to retrieve
	 * nested beans (bean lists: ownBean, sharedBean) without the need to
	 * rely on static calls to the facade (or make this class dep. on OODB).
	 *
	 * @param BeanHelper $helper helper to use for this bean
	 *
	 * @return void
	 */
	public function setBeanHelper( BeanHelper $helper )
	{
		$this->beanHelper = $helper;
	}

	/**
	 * Returns an ArrayIterator so you can treat the bean like
	 * an array with the properties container as its contents.
	 * This method is meant for PHP and allows you to access beans as if
	 * they were arrays, i.e. using array notation:
	 *
	 * <code>
	 * $bean[$key] = $value;
	 * </code>
	 *
	 * Note that not all PHP functions work with the array interface.
	 *
	 * @return \ArrayIterator
	 */
	 #[\ReturnTypeWillChange]
	public function getIterator()
	{
		return new \ArrayIterator( $this->properties );
	}

	/**
	 * Imports all values from an associative array $array. Chainable.
	 * This method imports the values in the first argument as bean
	 * property and value pairs. Use the second parameter to provide a
	 * selection. If a selection array is passed, only the entries
	 * having keys mentioned in the selection array will be imported.
	 * Set the third parameter to TRUE to preserve spaces in selection keys.
	 *
	 * @param array        $array     what you want to import
	 * @param string|array $selection selection of values
	 * @param boolean      $notrim    if TRUE selection keys will NOT be trimmed
	 *
	 * @return OODBBean
	 */
	public function import( $array, $selection = FALSE, $notrim = FALSE )
	{
		if ( is_string( $selection ) ) {
			$selection = explode( ',', $selection );
		}
		if ( is_array( $selection ) ) {
			if ( $notrim ) {
				$selected = array_flip($selection);
			} else {
				$selected = array();
				foreach ( $selection as $key => $select ) {
					$selected[trim( $select )] = TRUE;
				}
			}
		} else {
			$selected = FALSE;
		}
		foreach ( $array as $key => $value ) {
			if ( $key != '__info' ) {
				if ( !$selected || isset( $selected[$key] ) ) {
					if ( is_array($value ) ) {
						if ( isset( $value['_type'] ) ) {
							$bean = $this->beanHelper->getToolbox()->getRedBean()->dispense( $value['_type'] );
							unset( $value['_type'] );
							$bean->import($value);
							$this->$key = $bean;
						} else {
							$listBeans = array();
							foreach( $value as $listKey => $listItem ) {
								$bean = $this->beanHelper->getToolbox()->getRedBean()->dispense( $listItem['_type'] );
								unset( $listItem['_type'] );
								$bean->import($listItem);
								$list = &$this->$key;
								$list[ $listKey ] = $bean;
							}
						}
					} else {
						$this->$key = $value;
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Same as import() but trims all values by default.
	 * Set the second parameter to apply a different function.
	 *
	 * @param array        $array     what you want to import
	 * @param callable     $function  function to apply (default is trim)
	 * @param string|array $selection selection of values
	 * @param boolean      $notrim    if TRUE selection keys will NOT be trimmed
	 *
	 * @return OODBBean
	 */
	public function trimport( $array, $function='trim', $selection = FALSE, $notrim = FALSE ) {
		return $this->import( array_map( $function, $array ), $selection, $notrim );
	}

	/**
	* Imports an associative array directly into the
	* internal property array of the bean as well as the
	* meta property sys.orig and sets the changed flag to FALSE.
	* This is used by the repository objects to inject database rows
	* into the beans. It is not recommended to use this method outside
	* of a bean repository.
	*
	* @param array $row a database row
	*
	* @return self
	*/
	public function importRow( $row )
	{
		$this->properties = $row;
		$this->__info['sys.orig'] = $row;
		$this->__info['changed'] = FALSE;
		$this->__info['changelist'] = array();
		return $this;
	}

	/**
	 * Imports data from another bean. Chainable.
	 * Copies the properties from the source bean to the internal
	 * property list.
	 *
	 * Usage:
	 *
	 * <code>
	 * $copy->importFrom( $bean );
	 * </code>
	 *
	 * The example above demonstrates how to make a shallow copy
	 * of a bean using the importFrom() method.
	 *
	 * @param OODBBean $sourceBean the source bean to take properties from
	 *
	 * @return OODBBean
	 */
	public function importFrom( OODBBean $sourceBean )
	{
		$this->__info['tainted'] = TRUE;
		$this->__info['changed'] = TRUE;
		$this->properties = $sourceBean->properties;

		return $this;
	}

	/**
	 * Injects the properties of another bean but keeps the original ID.
	 * Just like import() but keeps the original ID.
	 * Chainable.
	 *
	 * @param OODBBean $otherBean the bean whose properties you would like to copy
	 *
	 * @return OODBBean
	 */
	public function inject( OODBBean $otherBean )
	{
		$myID = $this->properties['id'];
		$this->import( $otherBean->export( FALSE, FALSE, TRUE ) );
		$this->id = $myID;

		return $this;
	}

	/**
	 * Exports the bean as an array.
	 * This function exports the contents of a bean to an array and returns
	 * the resulting array. Depending on the parameters you can also
	 * export an entire graph of beans, apply filters or exclude meta data.
	 *
	 * Usage:
	 *
	 * <code>
	 * $bookData = $book->export( TRUE, TRUE, FALSE, [ 'author' ] );
	 * </code>
	 *
	 * The example above exports all bean properties to an array
	 * called $bookData including its meta data, parent objects but without
	 * any beans of type 'author'.
	 *
	 * @param boolean $meta    set to TRUE if you want to export meta data as well
	 * @param boolean $parents set to TRUE if you want to export parents as well
	 * @param boolean $onlyMe  set to TRUE if you want to export only this bean
	 * @param array   $filters optional whitelist for export
	 *
	 * @return array
	 */
	public function export( $meta = FALSE, $parents = FALSE, $onlyMe = FALSE, $filters = array() )
	{
		$arr = array();
		if ( $parents ) {
			foreach ( $this as $key => $value ) {
				if ( substr( $key, -3 ) != '_id' ) continue;

				$prop = substr( $key, 0, strlen( $key ) - 3 );
				$this->$prop;
			}
		}
		$hasFilters = is_array( $filters ) && count( $filters );
		foreach ( $this as $key => $value ) {
			if ( !$onlyMe && is_array( $value ) ) {
				$vn = array();

				foreach ( $value as $i => $b ) {
					if ( !( $b instanceof OODBBean ) ) continue;
					$vn[] = $b->export( $meta, FALSE, FALSE, $filters );
					$value = $vn;
				}
			} elseif ( $value instanceof OODBBean ) { if ( $hasFilters ) { //has to be on one line, otherwise code coverage miscounts as miss
					if ( !in_array( strtolower( $value->getMeta( 'type' ) ), $filters ) ) continue;
				}
				$value = $value->export( $meta, $parents, FALSE, $filters );
			}
			$arr[$key] = $value;
		}
		if ( $meta ) {
			$arr['__info'] = $this->__info;
		}
		return $arr;
	}

	/**
	 * Implements isset() function for use as an array.
	 * This allows you to use isset() on bean properties.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book->title = 'my book';
	 * echo isset($book['title']); //TRUE
	 * </code>
	 *
	 * The example illustrates how one can apply the
	 * isset() function to a bean.
	 *
	 * @param string $property name of the property you want to check
	 *
	 * @return boolean
	 */
	public function __isset( $property )
	{
		$property = $this->beau( $property );
		if ( strpos( $property, 'xown' ) === 0 && ctype_upper( substr( $property, 4, 1 ) ) ) {
			$property = substr($property, 1);
		}
		return isset( $this->properties[$property] );
	}

	/**
	 * Checks whether a related bean exists.
	 * For instance if a post bean has a related author, this method
	 * can be used to check if the author is set without loading the author.
	 * This method works by checking the related ID-field.
	 *
	 * @param string $property name of the property you wish to check
	 *
	 * @return boolean
	 */
	public function exists( $property )
	{
		$property = $this->beau( $property );
		/* fixes issue #549, see Base/Bean test */
		$hiddenRelationField = "{$property}_id";
		if ( array_key_exists( $hiddenRelationField, $this->properties ) ) {
			if ( !is_null( $this->properties[$hiddenRelationField] ) ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Returns the ID of the bean.
	 * If for some reason the ID has not been set, this method will
	 * return NULL. This is actually the same as accessing the
	 * id property using $bean->id. The ID of a bean is its primary
	 * key and should always correspond with a table column named
	 * 'id'.
	 *
	 * @return string|NULL
	 */
	public function getID()
	{
		return ( isset( $this->properties['id'] ) ) ? (string) $this->properties['id'] : NULL;
	}

	/**
	 * Unsets a property of a bean.
	 * Magic method, gets called implicitly when
	 * performing the unset() operation
	 * on a bean property.
	 *
	 * @param  string $property property to unset
	 *
	 * @return void
	 */
	public function __unset( $property )
	{
		$property = $this->beau( $property );

		if ( strpos( $property, 'xown' ) === 0 && ctype_upper( substr( $property, 4, 1 ) ) ) {
			$property = substr($property, 1);
		}
		unset( $this->properties[$property] );
		$shadowKey = 'sys.shadow.'.$property;
		if ( isset( $this->__info[ $shadowKey ] ) ) unset( $this->__info[$shadowKey] );
		//also clear modifiers
		$this->clearModifiers();
		return;
	}

	/**
	 * Returns the bean wrapped in an Either-instance.
	 * This allows the user to extract data from the bean using a chain
	 * of methods without any NULL checks, similar to the ?? operator but also
	 * in a way that is compatible with older versions of PHP.
	 * For more details consult the documentation of the Either class.
	 *
	 * @return Either
	 */
	public function either() {
		return new Either( $this );
	}

	/**
	 * Adds WHERE clause conditions to ownList retrieval.
	 * For instance to get the pages that belong to a book you would
	 * issue the following command: $book->ownPage
	 * However, to order these pages by number use:
	 *
	 * <code>
	 * $book->with(' ORDER BY `number` ASC ')->ownPage
	 * </code>
	 *
	 * the additional SQL snippet will be merged into the final
	 * query.
	 *
	 * @param string $sql      SQL to be added to retrieval query.
	 * @param array  $bindings array with parameters to bind to SQL snippet
	 *
	 * @return OODBBean
	 */
	public function with( $sql, $bindings = array() )
	{
		$this->withSql    = $sql;
		$this->withParams = $bindings;
		return $this;
	}

	/**
	 * Just like with(). Except that this method prepends the SQL query snippet
	 * with AND which makes it slightly more comfortable to use a conditional
	 * SQL snippet. For instance to filter an own-list with pages (belonging to
	 * a book) on specific chapters you can use:
	 *
	 * $book->withCondition(' chapter = 3 ')->ownPage
	 *
	 * This will return in the own list only the pages having 'chapter == 3'.
	 *
	 * @param string $sql      SQL to be added to retrieval query (prefixed by AND)
	 * @param array  $bindings array with parameters to bind to SQL snippet
	 *
	 * @return OODBBean
	 */
	public function withCondition( $sql, $bindings = array() )
	{
		$this->withSql    = ' AND ' . $sql;
		$this->withParams = $bindings;
		return $this;
	}

	/**
	 * Tells the bean to (re)load the following list without any
	 * conditions. If you have an ownList or sharedList with a
	 * condition you can use this method to reload the entire list.
	 *
	 * Usage:
	 *
	 * <code>
	 * $bean->with( ' LIMIT 3 ' )->ownPage; //Just 3
	 * $bean->all()->ownPage; //Reload all pages
	 * </code>
	 *
	 * @return self
	 */
	public function all()
	{
		$this->all = TRUE;
		return $this;
	}

	/**
	 * Tells the bean to only access the list but not load
	 * its contents. Use this if you only want to add something to a list
	 * and you have no interest in retrieving its contents from the database.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book->noLoad()->ownPage[] = $newPage;
	 * </code>
	 *
	 * In the example above we add the $newPage bean to the
	 * page list of book without loading all the pages first.
	 * If you know in advance that you are not going to use
	 * the contents of the list, you may use the noLoad() modifier
	 * to make sure the queries required to load the list will not
	 * be executed.
	 *
	 * @return self
	 */
	public function noLoad()
	{
		$this->noLoad = TRUE;
		return $this;
	}

	/**
	 * Prepares an own-list to use an alias. This is best explained using
	 * an example. Imagine a project and a person. The project always involves
	 * two persons: a teacher and a student. The person beans have been aliased in this
	 * case, so to the project has a teacher_id pointing to a person, and a student_id
	 * also pointing to a person. Given a project, we obtain the teacher like this:
	 *
	 * <code>
	 * $project->fetchAs('person')->teacher;
	 * </code>
	 *
	 * Now, if we want all projects of a teacher we cant say:
	 *
	 * <code>
	 * $teacher->ownProject
	 * </code>
	 *
	 * because the $teacher is a bean of type 'person' and no project has been
	 * assigned to a person. Instead we use the alias() method like this:
	 *
	 * <code>
	 * $teacher->alias('teacher')->ownProject
	 * </code>
	 *
	 * now we get the projects associated with the person bean aliased as
	 * a teacher.
	 *
	 * @param string $aliasName the alias name to use
	 *
	 * @return OODBBean
	 */
	public function alias( $aliasName )
	{
		$this->aliasName = $this->beau( $aliasName );
		return $this;
	}

	/**
	 * Returns properties of bean as an array.
	 * This method returns the raw internal property list of the
	 * bean. Only use this method for optimization purposes. Otherwise
	 * use the export() method to export bean data to arrays.
	 *
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * Returns properties of bean as an array.
	 * This method returns the raw internal property list of the
	 * bean. Only use this method for optimization purposes. Otherwise
	 * use the export() method to export bean data to arrays.
	 * This method returns an array with the properties array and
	 * the type (string).
	 *
	 * @return array
	 */
	public function getPropertiesAndType()
	{
		return array( $this->properties, $this->__info['type'] );
	}

	/**
	 * Turns a camelcase property name into an underscored property name.
	 *
	 * Examples:
	 *
	 * - oneACLRoute -> one_acl_route
	 * - camelCase -> camel_case
	 *
	 * Also caches the result to improve performance.
	 *
	 * @param string $property property to un-beautify
	 *
	 * @return string
	 */
	public function beau( $property )
	{
		static $beautifulColumns = array();

		if ( ctype_lower( $property ) ) return $property;
		if (
			( strpos( $property, 'own' ) === 0 && ctype_upper( substr( $property, 3, 1 ) ) )
			|| ( strpos( $property, 'xown' ) === 0 && ctype_upper( substr( $property, 4, 1 ) ) )
			|| ( strpos( $property, 'shared' ) === 0 && ctype_upper( substr( $property, 6, 1 ) ) )
		) {

			$property = preg_replace( '/List$/', '', $property );
			return $property;
		}
		if ( !isset( $beautifulColumns[$property] ) ) {
			$beautifulColumns[$property] = AQueryWriter::camelsSnake( $property );
		}
		return $beautifulColumns[$property];
	}

	/**
	 * Modifiers are a powerful concept in RedBeanPHP, they make it possible
	 * to change the way a property has to be loaded.
	 * RedBeanPHP uses property modifiers using a prefix notation like this:
	 *
	 * <code>
	 * $book->fetchAs('page')->cover;
	 * </code>
	 *
	 * Here, we load a bean of type page, identified by the cover property
	 * (or cover_id in the database). Because the modifier is called before
	 * the property is accessed, the modifier must be remembered somehow,
	 * this changes the state of the bean. Accessing a property causes the
	 * bean to clear its modifiers. To clear the modifiers manually you can
	 * use this method.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book->with( 'LIMIT 1' );
	 * $book->clearModifiers()->ownPageList;
	 * </code>
	 *
	 * In the example above, the 'LIMIT 1' clause is
	 * cleared before accessing the pages of the book, causing all pages
	 * to be loaded in the list instead of just one.
	 *
	 * @return self
	 */
	public function clearModifiers()
	{
		$this->withSql    = '';
		$this->withParams = array();
		$this->aliasName  = NULL;
		$this->fetchType  = NULL;
		$this->noLoad     = FALSE;
		$this->all        = FALSE;
		$this->via        = NULL;
		$this->castProperty = NULL;
		return $this;
	}

	/**
	 * Determines whether a list is opened in exclusive mode or not.
	 * If a list has been opened in exclusive mode this method will return TRUE,
	 * otherwise it will return FALSE.
	 *
	 * @param string $listName name of the list to check
	 *
	 * @return boolean
	 */
	public function isListInExclusiveMode( $listName )
	{
		$listName = $this->beau( $listName );

		if ( strpos( $listName, 'xown' ) === 0 && ctype_upper( substr( $listName, 4, 1 ) ) ) {
			$listName = substr($listName, 1);
		}
		$listName = lcfirst( substr( $listName, 3 ) );
		return ( isset( $this->__info['sys.exclusive-'.$listName] ) && $this->__info['sys.exclusive-'.$listName] );
	}

	/**
	 * Magic Getter. Gets the value for a specific property in the bean.
	 * If the property does not exist this getter will make sure no error
	 * occurs. This is because RedBean allows you to query (probe) for
	 * properties. If the property can not be found this method will
	 * return NULL instead.
	 *
	 * Usage:
	 *
	 * <code>
	 * $title = $book->title;
	 * $pages = $book->ownPageList;
	 * $tags  = $book->sharedTagList;
	 * </code>
	 *
	 * The example aboves lists several ways to invoke the magic getter.
	 * You can use the magic setter to access properties, own-lists,
	 * exclusive own-lists (xownLists) and shared-lists.
	 *
	 * @param string $property name of the property you wish to obtain the value of
	 *
	 * @return mixed
	 */
	public function &__get( $property )
	{
		$isEx          = FALSE;
		$isOwn         = FALSE;
		$isShared      = FALSE;
		if ( !ctype_lower( $property ) ) {
			$property = $this->beau( $property );
			if ( strpos( $property, 'xown' ) === 0 && ctype_upper( substr( $property, 4, 1 ) ) ) {
				$property = substr($property, 1);
				$listName = lcfirst( substr( $property, 3 ) );
				$isEx     = TRUE;
				$isOwn    = TRUE;
				$this->__info['sys.exclusive-'.$listName] = TRUE;
			} elseif ( strpos( $property, 'own' ) === 0 && ctype_upper( substr( $property, 3, 1 ) ) )  {
				$isOwn    = TRUE;
				$listName = lcfirst( substr( $property, 3 ) );
			} elseif ( strpos( $property, 'shared' ) === 0 && ctype_upper( substr( $property, 6, 1 ) ) ) {
				$isShared = TRUE;
			}
		}
		$fieldLink      = $property . '_id';
		$exists         = isset( $this->properties[$property] );

		//If not exists and no field link and no list, bail out.
		if ( !$exists && !isset($this->$fieldLink) && (!$isOwn && !$isShared )) {
			$this->clearModifiers();
			/**
			 * Github issue:
			 * Remove $NULL to directly return NULL #625
			 * @@ -1097,8 +1097,7 @@ public function &__get( $property )
			 *		$this->all        = FALSE;
			 *		$this->via        = NULL;
			 *
			 * - $NULL = NULL;
			 * - return $NULL;
			 * + return NULL;
			 *
			 * leads to regression:
			 * PHP Stack trace:
			 * PHP 1. {main}() testje.php:0
			 * PHP 2. RedBeanPHP\OODBBean->__get() testje.php:22
			 * Notice: Only variable references should be returned by reference in rb.php on line 2529
			 */
			$NULL = NULL;
			return $NULL;
		}

		$hasAlias       = (!is_null($this->aliasName));
		$differentAlias = ($hasAlias && $isOwn && isset($this->__info['sys.alias.'.$listName])) ?
									($this->__info['sys.alias.'.$listName] !== $this->aliasName) : FALSE;
		$hasSQL         = ($this->withSql !== '' || $this->via !== NULL);
		$hasAll         = (boolean) ($this->all);

		//If exists and no list or exits and list not changed, bail out.
		if ( $exists && ((!$isOwn && !$isShared ) || (!$hasSQL && !$differentAlias && !$hasAll)) ) {
			$castProperty = $this->castProperty;
			$this->clearModifiers();
			if (!is_null($castProperty)) {
				$object = new $castProperty( $this->properties[$property] );
				return $object;
			}
			return $this->properties[$property];
		}

		list( $redbean, , , $toolbox ) = $this->beanHelper->getExtractedToolbox();

		//If it's another bean, then we load it and return
		if ( isset( $this->$fieldLink ) ) {
			$this->__info['tainted'] = TRUE;
			if ( isset( $this->__info["sys.parentcache.$property"] ) ) {
				$bean = $this->__info["sys.parentcache.$property"];
			} else {
				if ( isset( self::$aliases[$property] ) ) {
					$type = self::$aliases[$property];
				} elseif ( $this->fetchType ) {
					$type = $this->fetchType;
					$this->fetchType = NULL;
				} else {
					$type = $property;
				}
				$bean = NULL;
				if ( !is_null( $this->properties[$fieldLink] ) ) {
					$bean = $redbean->load( $type, $this->properties[$fieldLink] );
				}
			}
			$this->properties[$property] = $bean;
			$this->clearModifiers();
			return $this->properties[$property];
		}

		/* Implicit: elseif ( $isOwn || $isShared ) */
		if ( $this->noLoad ) {
			$beans = array();
		} elseif ( $isOwn ) {
			$beans = $this->getOwnList( $listName, $redbean );
		} else {
			$beans = $this->getSharedList( lcfirst( substr( $property, 6 ) ), $redbean, $toolbox );
		}
		$this->properties[$property]          = $beans;
		$this->__info["sys.shadow.$property"] = $beans;
		$this->__info['tainted']              = TRUE;
		$this->clearModifiers();
		return $this->properties[$property];

	}

	/**
	 * Magic Setter. Sets the value for a specific property.
	 * This setter acts as a hook for OODB to mark beans as tainted.
	 * The tainted meta property can be retrieved using getMeta("tainted").
	 * The tainted meta property indicates whether a bean has been modified and
	 * can be used in various caching mechanisms.
	 *
	 * @param string $property name of the property you wish to assign a value to
	 * @param  mixed $value    the value you want to assign
	 *
	 * @return void
	 */
	public function __set( $property, $value )
	{
		$isEx          = FALSE;
		$isOwn         = FALSE;
		$isShared      = FALSE;

		if ( !ctype_lower( $property ) ) {
			$property = $this->beau( $property );
			if ( strpos( $property, 'xown' ) === 0 && ctype_upper( substr( $property, 4, 1 ) ) ) {
				$property = substr($property, 1);
				$listName = lcfirst( substr( $property, 3 ) );
				$isEx     = TRUE;
				$isOwn    = TRUE;
				$this->__info['sys.exclusive-'.$listName] = TRUE;
			} elseif ( strpos( $property, 'own' ) === 0 && ctype_upper( substr( $property, 3, 1 ) ) )  {
				$isOwn    = TRUE;
				$listName = lcfirst( substr( $property, 3 ) );
			} elseif ( strpos( $property, 'shared' ) === 0 && ctype_upper( substr( $property, 6, 1 ) ) ) {
				$isShared = TRUE;
			}
		} elseif ( self::$convertArraysToJSON && is_array( $value ) ) {
			$value = json_encode( $value );
		}

		$hasAlias       = (!is_null($this->aliasName));
		$differentAlias = ($hasAlias && $isOwn && isset($this->__info['sys.alias.'.$listName])) ?
								($this->__info['sys.alias.'.$listName] !== $this->aliasName) : FALSE;
		$hasSQL         = ($this->withSql !== '' || $this->via !== NULL);
		$exists         = isset( $this->properties[$property] );
		$fieldLink      = $property . '_id';
		$isFieldLink	= (($pos = strrpos($property, '_id')) !== FALSE) && array_key_exists( ($fieldName = substr($property, 0, $pos)), $this->properties );


		if ( ($isOwn || $isShared) &&  (!$exists || $hasSQL || $differentAlias) ) {

			if ( !$this->noLoad ) {
				list( $redbean, , , $toolbox ) = $this->beanHelper->getExtractedToolbox();
				if ( $isOwn ) {
					$beans = $this->getOwnList( $listName, $redbean );
				} else {
					$beans = $this->getSharedList( lcfirst( substr( $property, 6 ) ), $redbean, $toolbox );
				}
				$this->__info["sys.shadow.$property"] = $beans;
			}
		}

		$this->clearModifiers();

		$this->__info['tainted'] = TRUE;
		$this->__info['changed'] = TRUE;
		array_push( $this->__info['changelist'], $property );

		if ( array_key_exists( $fieldLink, $this->properties ) && !( $value instanceof OODBBean ) ) {
			if ( is_null( $value ) || $value === FALSE ) {

				unset( $this->properties[ $property ]);
				$this->properties[ $fieldLink ] = NULL;

				return;
			} else {
				throw new RedException( 'Cannot cast to bean.' );
			}
		}

		if ( $isFieldLink ){
			unset( $this->properties[ $fieldName ]);
			$this->properties[ $property ] = NULL;
		}


		if ( $value === FALSE ) {
			$value = '0';
		} elseif ( $value === TRUE ) {
			$value = '1';
			/* for some reason there is some kind of bug in xdebug so that it doesn't count this line otherwise... */
		} elseif ( ( ( $value instanceof \DateTime ) or ( $value instanceof \DateTimeInterface ) ) ) { $value = $value->format( 'Y-m-d H:i:s' ); }
		$this->properties[$property] = $value;
	}

	/**
	 * @deprecated
	 *
	 * Sets a property of the bean allowing you to keep track of
	 * the state yourself. This method sets a property of the bean and
	 * allows you to control how the state of the bean will be affected.
	 *
	 * While there may be some circumstances where this method is needed,
	 * this method is considered to be extremely dangerous.
	 * This method is only for advanced users.
	 *
	 * @param string  $property     property
	 * @param mixed   $value        value
	 * @param boolean $updateShadow whether you want to update the shadow
	 * @param boolean $taint        whether you want to mark the bean as tainted
	 *
	 * @return void
	 */
	public function setProperty( $property, $value, $updateShadow = FALSE, $taint = FALSE )
	{
		$this->properties[$property] = $value;

		if ( $updateShadow ) {
			$this->__info['sys.shadow.' . $property] = $value;
		}

		if ( $taint ) {
			$this->__info['tainted'] = TRUE;
			$this->__info['changed'] = TRUE;
		}
	}

	/**
	 * Returns the value of a meta property. A meta property
	 * contains additional information about the bean object that will not
	 * be stored in the database. Meta information is used to instruct
	 * RedBeanPHP as well as other systems how to deal with the bean.
	 * If the property cannot be found this getter will return NULL instead.
	 *
	 * Example:
	 *
	 * <code>
	 * $bean->setMeta( 'flush-cache', TRUE );
	 * </code>
	 *
	 * RedBeanPHP also stores meta data in beans, this meta data uses
	 * keys prefixed with 'sys.' (system).
	 *
	 * @param string $path    path to property in meta data
	 * @param mixed  $default default value
	 *
	 * @return mixed
	 */
	public function getMeta( $path, $default = NULL )
	{
		return ( isset( $this->__info[$path] ) ) ? $this->__info[$path] : $default;
	}

	/**
	 * Returns a value from the data bundle.
	 * The data bundle might contain additional data send from an SQL query,
	 * for instance, the total number of rows. If the property cannot be
	 * found, the default value will be returned. If no default has
	 * been specified, this method returns NULL.
	 *
	 * @param string $key     key
	 * @param mixed  $default default (defaults to NULL)
	 *
	 * @return mixed;
	 */
	public function info( $key, $default = NULL ) {
		return ( isset( $this->__info['data.bundle'][$key] ) ) ? $this->__info['data.bundle'][$key] : $default;
	}

	/**
	 * Gets and unsets a meta property.
	 * Moves a meta property out of the bean.
	 * This is a short-cut method that can be used instead
	 * of combining a get/unset.
	 *
	 * @param string $path    path to property in meta data
	 * @param mixed  $default default value
	 *
	 * @return mixed
	 */
	public function moveMeta( $path, $value = NULL )
	{
		if ( isset( $this->__info[$path] ) ) {
			$value = $this->__info[ $path ];
			unset( $this->__info[ $path ] );
		}
		return $value;
	}

	/**
	 * Stores a value in the specified Meta information property.
	 * The first argument should be the key to store the value under,
	 * the second argument should be the value. It is common to use
	 * a path-like notation for meta data in RedBeanPHP like:
	 * 'my.meta.data', however the dots are purely for readability, the
	 * meta data methods do not store nested structures or hierarchies.
	 *
	 * @param string $path  path / key to store value under
	 * @param mixed  $value value to store in bean (not in database) as meta data
	 *
	 * @return OODBBean
	 */
	public function setMeta( $path, $value )
	{
		$this->__info[$path] = $value;
		if ( $path == 'type' && !empty($this->beanHelper)) {
			$this->__info['model'] = $this->beanHelper->getModelForBean( $this );
		}

		return $this;
	}

	/**
	 * Copies the meta information of the specified bean
	 * This is a convenience method to enable you to
	 * exchange meta information easily.
	 *
	 * @param OODBBean $bean bean to copy meta data of
	 *
	 * @return OODBBean
	 */
	public function copyMetaFrom( OODBBean $bean )
	{
		$this->__info = $bean->__info;

		return $this;
	}

	/**
	 * Captures a dynamic casting.
	 * Enables you to obtain a bean value as an object by type-hinting
	 * the desired return object using asX where X is the class you wish
	 * to use as a wrapper for the property.
	 *
	 * Usage:
	 *
	 * $dateTime = $bean->asDateTime()->date;
	 *
	 * @param string $method method (asXXX)...
	 *
	 * @return self|NULL
	 */
	public function captureDynamicCasting( $method )
	{
		if ( strpos( $method, 'as' ) === 0 && ctype_upper( substr( $method, 2, 1) ) === TRUE ) {
			$this->castProperty = substr( $method, 2 );
			return $this;
		}
		return NULL;
	}

	/**
	 * Sends the call to the registered model.
	 * This method can also be used to override bean behaviour.
	 * In that case you don't want an error or exception to be triggered
	 * if the method does not exist in the model (because it's optional).
	 * Unfortunately we cannot add an extra argument to __call() for this
	 * because the signature is fixed. Another option would be to set
	 * a special flag ( i.e. $this->isOptionalCall ) but that would
	 * cause additional complexity because we have to deal with extra temporary state.
	 * So, instead I allowed the method name to be prefixed with '@', in practice
	 * nobody creates methods like that - however the '@' symbol in PHP is widely known
	 * to suppress error handling, so we can reuse the semantics of this symbol.
	 * If a method name gets passed starting with '@' the overrideDontFail variable
	 * will be set to TRUE and the '@' will be stripped from the function name before
	 * attempting to invoke the method on the model. This way, we have all the
	 * logic in one place.
	 *
	 * @param string $method name of the method
	 * @param array  $args   argument list
	 *
	 * @return mixed
	 */
	public function __call( $method, $args )
	{
		if ( empty( $this->__info['model'] ) ) {
			return $this->captureDynamicCasting($method);
		}

		$overrideDontFail = FALSE;
		if ( strpos( $method, '@' ) === 0 ) {
			$method = substr( $method, 1 );
			$overrideDontFail = TRUE;
		}

		if ( !is_callable( array( $this->__info['model'], $method ) ) ) {

			$self = $this->captureDynamicCasting($method);
			if ($self) return $self;

			if ( self::$errorHandlingFUSE === FALSE || $overrideDontFail ) {
				return NULL;
			}

			if ( in_array( $method, array( 'update', 'open', 'delete', 'after_delete', 'after_update', 'dispense' ), TRUE ) ) {
				return NULL;
			}

			$message = "FUSE: method does not exist in model: $method";
			if ( self::$errorHandlingFUSE === self::C_ERR_LOG ) {
				error_log( $message );
				return NULL;
			} elseif ( self::$errorHandlingFUSE === self::C_ERR_NOTICE ) {
				trigger_error( $message, E_USER_NOTICE );
				return NULL;
			} elseif ( self::$errorHandlingFUSE === self::C_ERR_WARN ) {
				trigger_error( $message, E_USER_WARNING );
				return NULL;
			} elseif ( self::$errorHandlingFUSE === self::C_ERR_EXCEPTION ) {
				throw new \Exception( $message );
			} elseif ( self::$errorHandlingFUSE === self::C_ERR_FUNC ) {
				$func = self::$errorHandler;
				return $func(array(
					'message' => $message,
					'method' => $method,
					'args' => $args,
					'bean' => $this
				));
			}
			trigger_error( $message, E_USER_ERROR );
			return NULL;
		}

		return call_user_func_array( array( $this->__info['model'], $method ), $args );
	}

	/**
	 * Implementation of __toString Method
	 * Routes call to Model. If the model implements a __toString() method this
	 * method will be called and the result will be returned. In case of an
	 * echo-statement this result will be printed. If the model does not
	 * implement a __toString method, this method will return a JSON
	 * representation of the current bean.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$string = $this->__call( '@__toString', array() );

		if ( $string === NULL ) {
			$list = array();
			foreach($this->properties as $property => $value) {
				if (is_scalar($value)) {
					if ( self::$enforceUTF8encoding ) {
						$list[$property] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
					} else {
						$list[$property] = $value;
					}
				}
			}
			$data = json_encode( $list );
			return $data;
		} else {
			return $string;
		}
	}

	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 * Call gets routed to __set.
	 *
	 * @param  mixed $offset offset string
	 * @param  mixed $value  value
	 *
	 * @return void
	 */
	 #[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value )
	{
		$this->__set( $offset, $value );
	}

	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 *
	 * Array functions do not reveal x-own-lists and list-alias because
	 * you dont want duplicate entries in foreach-loops.
	 * Also offers a slight performance improvement for array access.
	 *
	 * @param  mixed $offset property
	 *
	 * @return boolean
	 */
	 #[\ReturnTypeWillChange]
	public function offsetExists( $offset )
	{
		return $this->__isset( $offset );
	}

	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 * Unsets a value from the array/bean.
	 *
	 * Array functions do not reveal x-own-lists and list-alias because
	 * you don't want duplicate entries in foreach-loops.
	 * Also offers a slight performance improvement for array access.
	 *
	 * @param  mixed $offset property
	 *
	 * @return void
	 */
	 #[\ReturnTypeWillChange]
	public function offsetUnset( $offset )
	{
		$this->__unset( $offset );
	}

	/**
	 * Implementation of Array Access Interface, you can access bean objects
	 * like an array.
	 * Returns value of a property.
	 *
	 * Array functions do not reveal x-own-lists and list-alias because
	 * you don't want duplicate entries in foreach-loops.
	 * Also offers a slight performance improvement for array access.
	 *
	 * @param  mixed $offset property
	 *
	 * @return mixed
	 */
	 #[\ReturnTypeWillChange]
	public function &offsetGet( $offset )
	{
		return $this->__get( $offset );
	}

	/**
	 * Chainable method to cast a certain ID to a bean; for instance:
	 * $person = $club->fetchAs('person')->member;
	 * This will load a bean of type person using member_id as ID.
	 *
	 * @param  string $type preferred fetch type
	 *
	 * @return OODBBean
	 */
	public function fetchAs( $type )
	{
		$this->fetchType = $type;

		return $this;
	}

	/**
	 * Prepares to load a bean using the bean type specified by
	 * another property.
	 * Similar to fetchAs but uses a column instead of a direct value.
	 *
	 * Usage:
	 *
	 * <code>
	 * $car = R::load( 'car', $id );
	 * $engine = $car->poly('partType')->part;
	 * </code>
	 *
	 * In the example above, we have a bean of type car that
	 * may consists of several parts (i.e. chassis, wheels).
	 * To obtain the 'engine' we access the property 'part'
	 * using the type (i.e. engine) specified by the property
	 * indicated by the argument of poly().
	 * This essentially is a polymorph relation, hence the name.
	 * In database this relation might look like this:
	 *
	 * partType | part_id
	 * --------------------
	 * engine   | 1020300
	 * wheel    | 4820088
	 * chassis  | 7823122
	 *
	 * @param string $field field name to use for mapping
	 *
	 * @return OODBBean
	 */
	public function poly( $field )
	{
		return $this->fetchAs( $this->$field );
	}

	/**
	 * Traverses a bean property with the specified function.
	 * Recursively iterates through the property invoking the
	 * function for each bean along the way passing the bean to it.
	 *
	 * Can be used together with with, withCondition, alias and fetchAs.
	 *
	 * <code>
	 * $task
	 *    ->withCondition(' priority >= ? ', [ $priority ])
	 *    ->traverse('ownTaskList', function( $t ) use ( &$todo ) {
	 *       $todo[] = $t->descr;
	 *    } );
	 * </code>
	 *
	 * In the example, we create a to-do list by traversing a
	 * hierarchical list of tasks while filtering out all tasks
	 * having a low priority.
	 *
	 * @param string $property property
	 * @param callable $function function
	 * @param integer|NULL $maxDepth maximum depth for traversal
	 *
	 * @return OODBBean
	 * @throws RedException
	 */
	public function traverse( $property, $function, $maxDepth = NULL, $depth = 1 )
	{
		$this->via = NULL;
		if ( strpos( $property, 'shared' ) !== FALSE ) {
			throw new RedException( 'Traverse only works with (x)own-lists.' );
		}

		if ( !is_null( $maxDepth ) ) {
			if ( !$maxDepth-- ) return $this;
		}

		$oldFetchType = $this->fetchType;
		$oldAliasName = $this->aliasName;
		$oldWith      = $this->withSql;
		$oldBindings  = $this->withParams;

		$beans = $this->$property;

		if ( $beans === NULL ) return $this;

		if ( !is_array( $beans ) ) $beans = array( $beans );

		foreach( $beans as $bean ) {
			$function( $bean, $depth );
			$bean->fetchType  = $oldFetchType;
			$bean->aliasName  = $oldAliasName;
			$bean->withSql    = $oldWith;
			$bean->withParams = $oldBindings;

			$bean->traverse( $property, $function, $maxDepth, $depth + 1 );
		}

		return $this;
	}

	/**
	 * Implementation of Countable interface. Makes it possible to use
	 * count() function on a bean. This method gets invoked if you use
	 * the count() function on a bean. The count() method will return
	 * the number of properties of the bean, this includes the id property.
	 *
	 * Usage:
	 *
	 * <code>
	 * $bean = R::dispense('bean');
	 * $bean->property1 = 1;
	 * $bean->property2 = 2;
	 * echo count($bean); //prints 3 (cause id is also a property)
	 * </code>
	 *
	 * The example above will print the number 3 to stdout.
	 * Although we have assigned values to just two properties, the
	 * primary key id is also a property of the bean and together
	 * that makes 3. Besides using the count() function, you can also
	 * call this method using a method notation: $bean->count().
	 *
	 * @return integer
	 */
	 #[\ReturnTypeWillChange]
	public function count()
	{
		return count( $this->properties );
	}

	/**
	 * Checks whether a bean is empty or not.
	 * A bean is empty if it has no other properties than the id field OR
	 * if all the other properties are 'empty()' (this might
	 * include NULL and FALSE values).
	 *
	 * Usage:
	 *
	 * <code>
	 * $newBean = R::dispense( 'bean' );
	 * $newBean->isEmpty(); // TRUE
	 * </code>
	 *
	 * The example above demonstrates that newly dispensed beans are
	 * considered 'empty'.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		$empty = TRUE;
		foreach ( $this->properties as $key => $value ) {
			if ( $key == 'id' ) {
				continue;
			}
			if ( !empty( $value ) ) {
				$empty = FALSE;
			}
		}

		return $empty;
	}

	/**
	 * Chainable setter.
	 * This method is actually the same as just setting a value
	 * using a magic setter (->property = ...). The difference
	 * is that you can chain these setters like this:
	 *
	 * Usage:
	 *
	 * <code>
	 * $book->setAttr('title', 'mybook')->setAttr('author', 'me');
	 * </code>
	 *
	 * This is the same as setting both properties $book->title and
	 * $book->author. Sometimes a chained notation can improve the
	 * readability of the code.
	 *
	 * @param string $property the property of the bean
	 * @param mixed  $value    the value you want to set
	 *
	 * @return OODBBean
	 */
	public function setAttr( $property, $value )
	{
		$this->$property = $value;

		return $this;
	}

	/**
	 * Convenience method.
	 * Unsets all properties in the internal properties array.
	 *
	 * Usage:
	 *
	 * <code>
	 * $bean->property = 1;
	 * $bean->unsetAll( array( 'property' ) );
	 * $bean->property; //NULL
	 * </code>
	 *
	 * In the example above the 'property' of the bean will be
	 * unset, resulting in the getter returning NULL instead of 1.
	 *
	 * @param array $properties properties you want to unset.
	 *
	 * @return OODBBean
	 */
	public function unsetAll( $properties )
	{
		foreach ( $properties as $prop ) {
			if ( isset( $this->properties[$prop] ) ) {
				unset( $this->properties[$prop] );
			}
		}
		return $this;
	}

	/**
	 * Returns original (old) value of a property.
	 * You can use this method to see what has changed in a
	 * bean. The original value of a property is the value that
	 * this property has had since the bean has been retrieved
	 * from the databases.
	 *
	 * <code>
	 * $book->title = 'new title';
	 * $oldTitle = $book->old('title');
	 * </code>
	 *
	 * The example shows how to use the old() method.
	 * Here we set the title property of the bean to 'new title', then
	 * we obtain the original value using old('title') and store it in
	 * a variable $oldTitle.
	 *
	 * @param string $property name of the property you want the old value of
	 *
	 * @return mixed
	 */
	public function old( $property )
	{
		$old = $this->getMeta( 'sys.orig', array() );

		if ( array_key_exists( $property, $old ) ) {
			return $old[$property];
		}

		return NULL;
	}

	/**
	 * Convenience method.
	 *
	 * Returns TRUE if the bean has been changed, or FALSE otherwise.
	 * Same as $bean->getMeta('tainted');
	 * Note that a bean becomes tainted as soon as you retrieve a list from
	 * the bean. This is because the bean lists are arrays and the bean cannot
	 * determine whether you have made modifications to a list so RedBeanPHP
	 * will mark the whole bean as tainted.
	 *
	 * @return boolean
	 */
	public function isTainted()
	{
		return $this->getMeta( 'tainted' );
	}

	/**
	 * Returns TRUE if the value of a certain property of the bean has been changed and
	 * FALSE otherwise.
	 *
	 * Note that this method will return TRUE if applied to a loaded list.
	 * Also note that this method keeps track of the bean's history regardless whether
	 * it has been stored or not. Storing a bean does not undo its history,
	 * to clean the history of a bean use: clearHistory().
	 *
	 * @param string  $property name of the property you want the change-status of
	 *
	 * @return boolean
	 */
	public function hasChanged( $property )
	{
		return ( array_key_exists( $property, $this->properties ) ) ?
			$this->old( $property ) != $this->properties[$property] : FALSE;
	}

	/**
	 * Returns TRUE if the specified list exists, has been loaded
	 * and has been changed:
	 * beans have been added or deleted.
	 * This method will not tell you anything about
	 * the state of the beans in the list.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book->hasListChanged( 'ownPage' ); // FALSE
	 * array_pop( $book->ownPageList );
	 * $book->hasListChanged( 'ownPage' ); // TRUE
	 * </code>
	 *
	 * In the example, the first time we ask whether the
	 * own-page list has been changed we get FALSE. Then we pop
	 * a page from the list and the hasListChanged() method returns TRUE.
	 *
	 * @param string $property name of the list to check
	 *
	 * @return boolean
	 */
	public function hasListChanged( $property )
	{
		if ( !array_key_exists( $property, $this->properties ) ) return FALSE;
		$diffAdded = array_diff_assoc( $this->properties[$property], $this->__info['sys.shadow.'.$property] );
		if ( count( $diffAdded ) ) return TRUE;
		$diffMissing = array_diff_assoc( $this->__info['sys.shadow.'.$property], $this->properties[$property] );
		if ( count( $diffMissing ) ) return TRUE;
		return FALSE;
	}

	/**
	 * Clears (syncs) the history of the bean.
	 * Resets all shadow values of the bean to their current value.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book->title = 'book';
	 * echo $book->hasChanged( 'title' ); //TRUE
	 * R::store( $book );
	 * echo $book->hasChanged( 'title' ); //TRUE
	 * $book->clearHistory();
	 * echo $book->hasChanged( 'title' ); //FALSE
	 * </code>
	 *
	 * Note that even after store(), the history of the bean still
	 * contains the act of changing the title of the book.
	 * Only after invoking clearHistory() will the history of the bean
	 * be cleared and will hasChanged() return FALSE.
	 *
	 * @return self
	 */
	public function clearHistory()
	{
		$this->__info['sys.orig'] = array();
		foreach( $this->properties as $key => $value ) {
			if ( is_scalar($value) ) {
				$this->__info['sys.orig'][$key] = $value;
			} else {
				$this->__info['sys.shadow.'.$key] = $value;
			}
		}
		$this->__info[ 'changelist' ] = array();
		return $this;
	}

	/**
	 * Creates a N-M relation by linking an intermediate bean.
	 * This method can be used to quickly connect beans using indirect
	 * relations. For instance, given an album and a song you can connect the two
	 * using a track with a number like this:
	 *
	 * Usage:
	 *
	 * <code>
	 * $album->link('track', array('number'=>1))->song = $song;
	 * </code>
	 *
	 * or:
	 *
	 * <code>
	 * $album->link($trackBean)->song = $song;
	 * </code>
	 *
	 * What this method does is adding the link bean to the own-list, in this case
	 * ownTrack. If the first argument is a string and the second is an array or
	 * a JSON string then the linking bean gets dispensed on-the-fly as seen in
	 * example #1. After preparing the linking bean, the bean is returned thus
	 * allowing the chained setter: ->song = $song.
	 *
	 * @param string|OODBBean $typeOrBean    type of bean to dispense or the full bean
	 * @param string|array    $qualification JSON string or array (optional)
	 *
	 * @return OODBBean
	 */
	public function link( $typeOrBean, $qualification = array() )
	{
		if ( is_string( $typeOrBean ) ) {
			$typeOrBean = AQueryWriter::camelsSnake( $typeOrBean );
			$bean = $this->beanHelper->getToolBox()->getRedBean()->dispense( $typeOrBean );
			if ( is_string( $qualification ) ) {
				$data = json_decode( $qualification, TRUE );
			} else {
				$data = $qualification;
			}
			foreach ( $data as $key => $value ) {
				$bean->$key = $value;
			}
		} else {
			$bean = $typeOrBean;
		}
		$list = 'own' . ucfirst( $bean->getMeta( 'type' ) );
		array_push( $this->$list, $bean );
		return $bean;
	}

	/**
	 * Returns a bean of the given type with the same ID of as
	 * the current one. This only happens in a one-to-one relation.
	 * This is as far as support for 1-1 goes in RedBeanPHP. This
	 * method will only return a reference to the bean, changing it
	 * and storing the bean will not update the related one-bean.
	 *
	 * Usage:
	 *
	 * <code>
	 * $author = R::load( 'author', $id );
	 * $biography = $author->one( 'bio' );
	 * </code>
	 *
	 * The example loads the biography associated with the author
	 * using a one-to-one relation. These relations are generally not
	 * created (nor supported) by RedBeanPHP.
	 *
	 * @param  $type type of bean to load
	 *
	 * @return OODBBean
	 */
	public function one( $type ) {
		return $this->beanHelper
			->getToolBox()
			->getRedBean()
			->load( $type, $this->id );
	}

	/**
	 * Reloads the bean.
	 * Returns the same bean freshly loaded from the database.
	 * This method is equal to the following code:
	 *
	 * <code>
	 * $id = $bean->id;
	 * $type = $bean->getMeta( 'type' );
	 * $bean = R::load( $type, $id );
	 * </code>
	 *
	 * This is just a convenience method to reload beans
	 * quickly.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::exec( ...update query... );
	 * $book = $book->fresh();
	 * </code>
	 *
	 * The code snippet above illustrates how to obtain changes
	 * caused by an UPDATE query, simply by reloading the bean using
	 * the fresh() method.
	 *
	 * @return OODBBean
	 */
	public function fresh()
	{
		return $this->beanHelper
			->getToolbox()
			->getRedBean()
			->load( $this->getMeta( 'type' ), $this->properties['id'] );
	}

	/**
	 * Registers a association renaming globally.
	 * Use via() and link() to associate shared beans using a
	 * 3rd bean that will act as an intermediate type. For instance
	 * consider an employee and a project. We could associate employees
	 * with projects using a sharedEmployeeList. But, maybe there is more
	 * to the relationship than just the association. Maybe we want
	 * to qualify the relation between a project and an employee with
	 * a role: 'developer', 'designer', 'tester' and so on. In that case,
	 * it might be better to introduce a new concept to reflect this:
	 * the participant. However, we still want the flexibility to
	 * query our employees in one go. This is where link() and via()
	 * can help. You can still introduce the more applicable
	 * concept (participant) and have your easy access to the shared beans.
	 *
	 * <code>
	 * $Anna = R::dispense( 'employee' );
	 * $Anna->badge   = 'Anna';
	 * $project = R::dispense( 'project' );
	 * $project->name = 'x';
	 * $Anna->link( 'participant', array(
	 *	 'arole' => 'developer'
	 *	) )->project = $project;
	 * R::storeAll( array( $project,  $Anna )  );
	 * $employees = $project
	 *	->with(' ORDER BY badge ASC ')
	 *  ->via( 'participant' )
	 *  ->sharedEmployee;
	 * </code>
	 *
	 * This piece of code creates a project and an employee.
	 * It then associates the two using a via-relation called
	 * 'participant' ( employee <-> participant <-> project ).
	 * So, there will be a table named 'participant' instead of
	 * a table named 'employee_project'. Using the via() method, the
	 * employees associated with the project are retrieved 'via'
	 * the participant table (and an SQL snippet to order them by badge).
	 *
	 * @param string $via type you wish to use for shared lists
	 *
	 * @return OODBBean
	 */
	public function via( $via )
	{
		$this->via = AQueryWriter::camelsSnake( $via );

		return $this;
	}

	/**
	 * Counts all own beans of type $type.
	 * Also works with alias(), with() and withCondition().
	 * Own-beans or xOwn-beans (exclusively owned beans) are beans
	 * that have been associated using a one-to-many relation. They can
	 * be accessed through the ownXList where X is the type of the
	 * associated beans.
	 *
	 * Usage:
	 *
	 * <code>
	 * $Bill->alias( 'author' )
	 *      ->countOwn( 'book' );
	 * </code>
	 *
	 * The example above counts all the books associated with 'author'
	 * $Bill.
	 *
	 * @param string $type the type of bean you want to count
	 *
	 * @return integer
	 */
	public function countOwn( $type )
	{
		$type = $this->beau( $type );
		if ( $this->aliasName ) {
			$myFieldLink     = $this->aliasName . '_id';
			$this->aliasName = NULL;
		} else {
			$myFieldLink = $this->__info['type'] . '_id';
		}
		$count = 0;
		if ( $this->getID() ) {
			reset( $this->withParams );
			$firstKey = count( $this->withParams ) > 0
				? key( $this->withParams )
				: 0;
			if ( is_int( $firstKey ) ) {
				$sql = "{$myFieldLink} = ? {$this->withSql}";
				$bindings = array_merge( array( $this->getID() ), $this->withParams );
			} else {
				$sql = "{$myFieldLink} = :slot0 {$this->withSql}";
				$bindings           = $this->withParams;
				$bindings[':slot0'] = $this->getID();
			}
			if ( !self::$useFluidCount ) {
				$count = $this->beanHelper->getToolbox()->getWriter()->queryRecordCount( $type, array(), $sql, $bindings );
			} else {
				$count = $this->beanHelper->getToolbox()->getRedBean()->count( $type, $sql, $bindings );
			}
		}
		$this->clearModifiers();
		return (int) $count;
	}

	/**
	 * Counts all shared beans of type $type.
	 * Also works with via(), with() and withCondition().
	 * Shared beans are beans that have an many-to-many relation.
	 * They can be accessed using the sharedXList, where X the
	 * type of the shared bean.
	 *
	 * Usage:
	 *
	 * <code>
	 * $book = R::dispense( 'book' );
	 * $book->sharedPageList = R::dispense( 'page', 5 );
	 * R::store( $book );
	 * echo $book->countShared( 'page' );
	 * </code>
	 *
	 * The code snippet above will output '5', because there
	 * are 5 beans of type 'page' in the shared list.
	 *
	 * @param string $type type of bean you wish to count
	 *
	 * @return integer
	 */
	public function countShared( $type )
	{
		$toolbox = $this->beanHelper->getToolbox();
		$redbean = $toolbox->getRedBean();
		$writer  = $toolbox->getWriter();
		if ( $this->via ) {
			$oldName = $writer->getAssocTable( array( $this->__info['type'], $type ) );
			if ( $oldName !== $this->via ) {
				//set the new renaming rule
				$writer->renameAssocTable( $oldName, $this->via );
				$this->via = NULL;
			}
		}
		$type  = $this->beau( $type );
		$count = 0;
		if ( $this->getID() ) {
			$count = $redbean->getAssociationManager()->relatedCount( $this, $type, $this->withSql, $this->withParams );
		}
		$this->clearModifiers();
		return (integer) $count;
	}

	/**
	 * Iterates through the specified own-list and
	 * fetches all properties (with their type) and
	 * returns the references.
	 * Use this method to quickly load indirectly related
	 * beans in an own-list. Whenever you cannot use a
	 * shared-list this method offers the same convenience
	 * by aggregating the parent beans of all children in
	 * the specified own-list.
	 *
	 * Example:
	 *
	 * <code>
	 * $quest->aggr( 'xownQuestTarget', 'target', 'quest' );
	 * </code>
	 *
	 * Loads (in batch) and returns references to all
	 * quest beans residing in the $questTarget->target properties
	 * of each element in the xownQuestTargetList.
	 *
	 * @param string      $list     the list you wish to process
	 * @param string      $property the property to load
	 * @param string|NULL $type     the type of bean residing in this property (optional)
	 *
	 * @return array
	 */
	public function &aggr( $list, $property, $type = NULL )
	{
		$this->via = NULL;
		$ids = $beanIndex = $references = array();

		if ( strlen( $list ) < 4 ) throw new RedException('Invalid own-list.');
		if ( strpos( $list, 'own') !== 0 ) throw new RedException('Only own-lists can be aggregated.');
		if ( !ctype_upper( substr( $list, 3, 1 ) ) ) throw new RedException('Invalid own-list.');

		if ( is_null( $type ) ) $type = $property;

		foreach( $this->$list as $bean ) {
			$field = $property . '_id';
			if ( isset( $bean->$field)  ) {
				$ids[] = $bean->$field;
				$beanIndex[$bean->$field] = $bean;
			}
		}

		$beans = $this->beanHelper->getToolBox()->getRedBean()->batch( $type, $ids );

		//now preload the beans as well
		foreach( $beans as $bean ) {
			$beanIndex[$bean->id]->setProperty( $property, $bean );
		}

		foreach( $beanIndex as $indexedBean ) {
			$references[] = $indexedBean->$property;
		}

		return $references;
	}

	/**
	 * Tests whether the database identities of two beans are equal.
	 * Two beans are considered 'equal' if:
	 *
	 * a. the types of the beans match
	 * b. the ids of the beans match
	 *
	 * Returns TRUE if the beans are considered equal according to this
	 * specification and FALSE otherwise.
	 *
	 * Usage:
	 *
	 * <code>
	 * $coffee->fetchAs( 'flavour' )->taste->equals(
	 *    R::enum('flavour:mocca')
	 * );
	 * </code>
	 *
	 * The example above compares the flavour label 'mocca' with
	 * the flavour label attached to the $coffee bean. This illustrates
	 * how to use equals() with RedBeanPHP-style enums.
	 *
	 * @param OODBBean|null $bean other bean
	 *
	 * @return boolean
	 */
	public function equals(OODBBean $bean)
	{
		if ( is_null($bean) ) return false;

		return (bool) (
			   ( (string) $this->properties['id'] === (string) $bean->properties['id'] )
			&& ( (string) $this->__info['type']   === (string) $bean->__info['type']   )
		);
	}

	/**
	 * Magic method jsonSerialize,
	 * implementation for the \JsonSerializable interface,
	 * this method gets called by json_encode and
	 * facilitates a better JSON representation
	 * of the bean. Exports the bean on JSON serialization,
	 * for the JSON fans.
	 *
	 * Models can override jsonSerialize (issue #651) by
	 * implementing a __jsonSerialize method which should return
	 * an array. The __jsonSerialize override gets called with
	 * the @ modifier to prevent errors or warnings.
	 *
	 * @see  http://php.net/manual/en/class.jsonserializable.php
	 *
	 * @return array
	 */
	 #[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		$json = $this->__call( '@__jsonSerialize', array( ) );

		if ( $json !== NULL ) {
			return $json;
		}

		return $this->export();
	}
}
}

namespace RedBeanPHP {

use RedBeanPHP\Observer as Observer;

/**
 * Observable
 * Base class for Observables
 *
 * @file            RedBeanPHP/Observable.php
 * @author          Gabor de Mooij and the RedBeanPHP community
 * @license         BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
abstract class Observable { //bracket must be here - otherwise coverage software does not understand.

	/**
	 * @var array
	 */
	private $observers = array();

	/**
	 * Implementation of the Observer Pattern.
	 * Adds an event listener to the observable object.
	 * First argument should be the name of the event you wish to listen for.
	 * Second argument should be the object that wants to be notified in case
	 * the event occurs.
	 *
	 * @param string   $eventname event identifier
	 * @param Observer $observer  observer instance
	 *
	 * @return void
	 */
	public function addEventListener( $eventname, Observer $observer )
	{
		if ( !isset( $this->observers[$eventname] ) ) {
			$this->observers[$eventname] = array();
		}

		if ( in_array( $observer, $this->observers[$eventname] ) ) {
			return;
		}

		$this->observers[$eventname][] = $observer;
	}

	/**
	 * Notifies listeners.
	 * Sends the signal $eventname, the event identifier and a message object
	 * to all observers that have been registered to receive notification for
	 * this event. Part of the observer pattern implementation in RedBeanPHP.
	 *
	 * @param string $eventname event you want signal
	 * @param mixed  $info      message object to send along
	 *
	 * @return void
	 */
	public function signal( $eventname, $info )
	{
		if ( !isset( $this->observers[$eventname] ) ) {
			$this->observers[$eventname] = array();
		}

		foreach ( $this->observers[$eventname] as $observer ) {
			$observer->onEvent( $eventname, $info );
		}
	}
}
}

namespace RedBeanPHP {

/**
 * Observer.
 *
 * Interface for Observer object. Implementation of the
 * observer pattern.
 *
 * @file    RedBeanPHP/Observer.php
 * @author  Gabor de Mooij and the RedBeanPHP community
 * @license BSD/GPLv2
 * @desc    Part of the observer pattern in RedBean
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface Observer
{
	/**
	 * An observer object needs to be capable of receiving
	 * notifications. Therefore the observer needs to implement the
	 * onEvent method with two parameters: the event identifier specifying the
	 * current event and a message object (in RedBeanPHP this can also be a bean).
	 *
	 * @param string $eventname event identifier
	 * @param mixed  $bean      a message sent along with the notification
	 *
	 * @return void
	 */
	public function onEvent( $eventname, $bean );
}
}

namespace RedBeanPHP {

/**
 * Adapter Interface.
 * Describes the API for a RedBeanPHP Database Adapter.
 * This interface defines the API contract for
 * a RedBeanPHP Database Adapter.
 *
 * @file    RedBeanPHP/Adapter.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface Adapter
{
	/**
	 * Should returns a string containing the most recent SQL query
	 * that has been processed by the adapter.
	 *
	 * @return string
	 */
	public function getSQL();

	/**
	 * Executes an SQL Statement using an array of values to bind
	 * If $noevent is TRUE then this function will not signal its
	 * observers to notify about the SQL execution; this to prevent
	 * infinite recursion when using observers.
	 *
	 * @param string  $sql      string containing SQL code for database
	 * @param array   $bindings array of values to bind to parameters in query string
	 * @param boolean $noevent  no event firing
	 *
	 * @return int
	 */
	public function exec( $sql, $bindings = array(), $noevent = FALSE );

	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a multi dimensional resultset similar to getAll
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql      string containing SQL code for database
	 * @param array  $bindings array of values to bind to parameters in query string
	 *
	 * @return array
	 */
	public function get( $sql, $bindings = array() );

	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a single row (one array) resultset.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql      string containing SQL code for database
	 * @param array  $bindings array of values to bind to parameters in query string
	 *
	 * @return array|NULL
	 */
	public function getRow( $sql, $bindings = array() );

	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a single column (one array) resultset.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql      string containing SQL code for database
	 * @param array  $bindings array of values to bind to parameters in query string
	 *
	 * @return array
	 */
	public function getCol( $sql, $bindings = array() );

	/**
	 * Executes an SQL Query and returns a resultset.
	 * This method returns a single cell, a scalar value as the resultset.
	 * The values array can be used to bind values to the place holders in the
	 * SQL query.
	 *
	 * @param string $sql      string containing SQL code for database
	 * @param array  $bindings array of values to bind to parameters in query string
	 *
	 * @return string|NULL
	 */
	public function getCell( $sql, $bindings = array() );

	/**
	 * Executes the SQL query specified in $sql and indexes
	 * the row by the first column.
	 *
	 * @param string $sql      string containing SQL code for database
	 * @param array  $bindings array of values to bind to parameters in query string
	 *
	 * @return array
	 */
	public function getAssoc( $sql, $bindings = array() );

	/**
	 * Executes the SQL query specified in $sql and returns
	 * an associative array where the column names are the keys.
	 *
	 * @param string $sql      String containing SQL code for databaseQL
	 * @param array  $bindings values to bind
	 *
	 * @return array
	 */
	public function getAssocRow( $sql, $bindings = array() );

	/**
	 * Returns the latest insert ID.
	 *
	 * @return integer
	 */
	public function getInsertID();

	/**
	 * Returns the number of rows that have been
	 * affected by the last update statement.
	 *
	 * @return integer
	 */
	public function getAffectedRows();

	/**
	 * Returns a database agnostic Cursor object.
	 *
	 * @param string $sql      string containing SQL code for database
	 * @param array  $bindings array of values to bind to parameters in query string
	 *
	 * @return Cursor
	 */
	public function getCursor( $sql, $bindings = array() );

	/**
	 * Returns the original database resource. This is useful if you want to
	 * perform operations on the driver directly instead of working with the
	 * adapter. RedBean will only access the adapter and never to talk
	 * directly to the driver though.
	 *
	 * @return Driver
	 */
	public function getDatabase();

	/**
	 * This method is part of the RedBean Transaction Management
	 * mechanisms.
	 * Starts a transaction.
	 *
	 * @return void
	 */
	public function startTransaction();

	/**
	 * This method is part of the RedBean Transaction Management
	 * mechanisms.
	 * Commits the transaction.
	 *
	 * @return void
	 */
	public function commit();

	/**
	 * This method is part of the RedBean Transaction Management
	 * mechanisms.
	 * Rolls back the transaction.
	 *
	 * @return void
	 */
	public function rollback();

	/**
	 * Closes database connection.
	 *
	 * @return void
	 */
	public function close();

	/**
	 * Sets a driver specific option.
	 * Using this method you can access driver-specific functions.
	 * If the selected option exists the value will be passed and
	 * this method will return boolean TRUE, otherwise it will return
	 * boolean FALSE.
	 *
	 * @param string $optionKey   option key
	 * @param string $optionValue option value
	 *
	 * @return boolean
	 */
	public function setOption( $optionKey, $optionValue );

	/**
	 * Returns the version string from the database server.
	 *
	 * @return string
	 */
	public function getDatabaseServerVersion();
}
}

namespace RedBeanPHP\Adapter {

use RedBeanPHP\Observable as Observable;
use RedBeanPHP\Adapter as Adapter;
use RedBeanPHP\Driver as Driver;

/**
 * DBAdapter (Database Adapter)
 *
 * An adapter class to connect various database systems to RedBean
 * Database Adapter Class. The task of the database adapter class is to
 * communicate with the database driver. You can use all sorts of database
 * drivers with RedBeanPHP. The default database drivers that ships with
 * the RedBeanPHP library is the RPDO driver ( which uses the PHP Data Objects
 * Architecture aka PDO ).
 *
 * @file    RedBeanPHP/Adapter/DBAdapter.php
 * @author  Gabor de Mooij and the RedBeanPHP Community.
 * @license BSD/GPLv2
 *
 * @copyright
 * (c) copyright G.J.G.T. (Gabor) de Mooij and the RedBeanPHP community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class DBAdapter extends Observable implements Adapter
{
	/**
	 * @var Driver
	 */
	private $db = NULL;

	/**
	 * @var string
	 */
	private $sql = '';

	/**
	 * Constructor.
	 *
	 * Creates an instance of the RedBean Adapter Class.
	 * This class provides an interface for RedBean to work
	 * with ADO compatible DB instances.
	 *
	 * Usage:
	 *
	 * <code>
	 * $database = new RPDO( $dsn, $user, $pass );
	 * $adapter = new DBAdapter( $database );
	 * $writer = new PostgresWriter( $adapter );
	 * $oodb = new OODB( $writer, FALSE );
	 * $bean = $oodb->dispense( 'bean' );
	 * $bean->name = 'coffeeBean';
	 * $id = $oodb->store( $bean );
	 * $bean = $oodb->load( 'bean', $id );
	 * </code>
	 *
	 * The example above creates the 3 RedBeanPHP core objects:
	 * the Adapter, the Query Writer and the OODB instance and
	 * wires them together. The example also demonstrates some of
	 * the methods that can be used with OODB, as you see, they
	 * closely resemble their facade counterparts.
	 *
	 * The wiring process: create an RPDO instance using your database
	 * connection parameters. Create a database adapter from the RPDO
	 * object and pass that to the constructor of the writer. Next,
	 * create an OODB instance from the writer. Now you have an OODB
	 * object.
	 *
	 * @param Driver $database ADO Compatible DB Instance
	 */
	public function __construct( $database )
	{
		$this->db = $database;
	}

	/**
	 * Returns a string containing the most recent SQL query
	 * processed by the database adapter, thus conforming to the
	 * interface:
	 *
	 * @see Adapter::getSQL
	 *
	 * Methods like get(), getRow() and exec() cause this SQL cache
	 * to get filled. If no SQL query has been processed yet this function
	 * will return an empty string.
	 *
	 * @return string
	 */
	public function getSQL()
	{
		return $this->sql;
	}

	/**
	 * @see Adapter::exec
	 */
	public function exec( $sql, $bindings = array(), $noevent = FALSE )
	{
		if ( !$noevent ) {
			$this->sql = $sql;
			$this->signal( 'sql_exec', $this );
		}

		return $this->db->Execute( $sql, $bindings );
	}

	/**
	 * @see Adapter::get
	 */
	public function get( $sql, $bindings = array() )
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetAll( $sql, $bindings );
	}

	/**
	 * @see Adapter::getRow
	 */
	public function getRow( $sql, $bindings = array() )
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetRow( $sql, $bindings );
	}

	/**
	 * @see Adapter::getCol
	 */
	public function getCol( $sql, $bindings = array() )
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetCol( $sql, $bindings );
	}

	/**
	 * @see Adapter::getAssoc
	 */
	public function getAssoc( $sql, $bindings = array() )
	{
		$this->sql = $sql;

		$this->signal( 'sql_exec', $this );

		$rows  = $this->db->GetAll( $sql, $bindings );

		if ( !$rows ) return array();

		$assoc = array();

		foreach ( $rows as $row ) {
			if ( empty( $row ) ) continue;

			$key   = array_shift( $row );
			switch ( count( $row ) ) {
				case 0:
					$value = $key;
					break;
				case 1:
					$value = reset( $row );
					break;
				default:
					$value = $row;
			}

			$assoc[$key] = $value;
		}

		return $assoc;
	}

	/**
	 * @see Adapter::getAssocRow
	 */
	public function getAssocRow($sql, $bindings = array())
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetAssocRow( $sql, $bindings );
	}

	/**
	 * @see Adapter::getCell
	 */
	public function getCell( $sql, $bindings = array(), $noSignal = NULL )
	{
		$this->sql = $sql;

		if ( !$noSignal ) $this->signal( 'sql_exec', $this );

		return $this->db->GetOne( $sql, $bindings );
	}

	/**
	 * @see Adapter::getCursor
	 */
	public function getCursor( $sql, $bindings = array() )
	{
		return $this->db->GetCursor( $sql, $bindings );
	}

	/**
	 * @see Adapter::getInsertID
	 */
	public function getInsertID()
	{
		return $this->db->getInsertID();
	}

	/**
	 * @see Adapter::getAffectedRows
	 */
	public function getAffectedRows()
	{
		return $this->db->Affected_Rows();
	}

	/**
	 * @see Adapter::getDatabase
	 */
	public function getDatabase()
	{
		return $this->db;
	}

	/**
	 * @see Adapter::startTransaction
	 */
	public function startTransaction()
	{
		$this->db->StartTrans();
	}

	/**
	 * @see Adapter::commit
	 */
	public function commit()
	{
		$this->db->CommitTrans();
	}

	/**
	 * @see Adapter::rollback
	 */
	public function rollback()
	{
		$this->db->FailTrans();
	}

	/**
	 * @see Adapter::close.
	 */
	public function close()
	{
		$this->db->close();
	}

	/**
	 * Sets initialization code for connection.
	 *
	 * @param callable $code
	 */
	public function setInitCode($code) {
		$this->db->setInitCode($code);
	}

	/**
	 * @see Adapter::setOption
	 */
	public function setOption( $optionKey, $optionValue ) {
		if ( method_exists( $this->db, $optionKey ) ) {
			call_user_func( array( $this->db, $optionKey ), $optionValue );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @see Adapter::getDatabaseServerVersion
	 */
	public function getDatabaseServerVersion()
	{
		return $this->db->DatabaseServerVersion();
	}
}
}

namespace RedBeanPHP {

/**
 * Database Cursor Interface.
 * A cursor is used by Query Writers to fetch Query Result rows
 * one row at a time. This is useful if you expect the result set to
 * be quite large. This interface describes the API of a database
 * cursor. There can be multiple implementations of the Cursor,
 * by default RedBeanPHP offers the PDOCursor for drivers shipping
 * with RedBeanPHP and the NULLCursor.
 *
 * @file    RedBeanPHP/Cursor.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface Cursor
{
	/**
	 * Should retrieve the next row of the result set.
	 * This method is used to iterate over the result set.
	 *
	 * @return array|NULL
	 */
	public function getNextItem();

	/**
	 * Resets the cursor by closing it and re-executing the statement.
	 * This reloads fresh data from the database for the whole collection.
	 *
	 * @return void
	 */
	public function reset();

	/**
	 * Closes the database cursor.
	 * Some databases require a cursor to be closed before executing
	 * another statement/opening a new cursor.
	 *
	 * @return void
	 */
	public function close();
}
}

namespace RedBeanPHP\Cursor {

use RedBeanPHP\Cursor as Cursor;

/**
 * PDO Database Cursor
 * Implementation of PDO Database Cursor.
 * Used by the BeanCollection to fetch one bean at a time.
 * The PDO Cursor is used by Query Writers to support retrieval
 * of large bean collections. For instance, this class is used to
 * implement the findCollection()/BeanCollection functionality.
 *
 * @file    RedBeanPHP/Cursor/PDOCursor.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class PDOCursor implements Cursor
{
	/**
	 * @var \PDOStatement
	 */
	protected $res;

	/**
	 * @var string
	 */
	protected $fetchStyle;

	/**
	 * Constructor, creates a new instance of a PDO Database Cursor.
	 *
	 * @param \PDOStatement $res        the PDO statement
	 * @param string       $fetchStyle fetch style constant to use
	 *
	 * @return void
	 */
	public function __construct( \PDOStatement $res, $fetchStyle )
	{
		$this->res = $res;
		$this->fetchStyle = $fetchStyle;
	}

	/**
	 * @see Cursor::getNextItem
	 */
	public function getNextItem()
	{
		return $this->res->fetch();
	}

	/**
	 * @see Cursor::reset
	 */
	public function reset()
	{
		$this->close();
		$this->res->execute();
	}

	/**
	 * @see Cursor::close
	 */
	public function close()
	{
		$this->res->closeCursor();
	}
}
}

namespace RedBeanPHP\Cursor {

use RedBeanPHP\Cursor as Cursor;

/**
 * NULL Database Cursor
 * Implementation of the NULL Cursor.
 * Used for an empty BeanCollection. This Cursor
 * can be used for instance if a query fails but the interface
 * demands a cursor to be returned.
 *
 * @file    RedBeanPHP/Cursor/NULLCursor.php
 * @author  Gabor de Mooij and the RedBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class NullCursor implements Cursor
{
	/**
	 * @see Cursor::getNextItem
	 */
	public function getNextItem()
	{
		return NULL;
	}

	/**
	 * @see Cursor::reset
	 */
	public function reset()
	{
		return NULL;
	}

	/**
	 * @see Cursor::close
	 */
	public function close()
	{
		return NULL;
	}
}
}

namespace RedBeanPHP {

use RedBeanPHP\Cursor as Cursor;
use RedBeanPHP\Repository as Repository;

/**
 * BeanCollection.
 *
 * The BeanCollection represents a collection of beans and
 * makes it possible to use database cursors. The BeanCollection
 * has a method next() to obtain the first, next and last bean
 * in the collection. The BeanCollection does not implement the array
 * interface nor does it try to act like an array because it cannot go
 * backward or rewind itself.
 *
 * Use the BeanCollection for large datasets where skip/limit is not an
 * option. Keep in mind that ID-marking (querying a start ID) is a decent
 * alternative though.
 *
 * @file    RedBeanPHP/BeanCollection.php
 * @author  Gabor de Mooij and the RedBeanPHP community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class BeanCollection
{
	/**
	 * @var Cursor
	 */
	protected $cursor = NULL;

	/**
	 * @var Repository
	 */
	protected $repository = NULL;

	/**
	 * @var string
	 */
	protected $type = NULL;

	/**
	 * @var string
	 */
	protected $mask = NULL;

	/**
	 * Constructor, creates a new instance of the BeanCollection.
	 *
	 * @param string     $type       type of beans in this collection
	 * @param Repository $repository repository to use to generate bean objects
	 * @param Cursor     $cursor     cursor object to use
	 * @param string     $mask       meta mask to apply (optional)
	 *
	 * @return void
	 */
	public function __construct( $type, Repository $repository, Cursor $cursor, $mask = '__meta' )
	{
		$this->type = $type;
		$this->cursor = $cursor;
		$this->repository = $repository;
		$this->mask = $mask;
	}

	/**
	 * Returns the next bean in the collection.
	 * If called the first time, this will return the first bean in the collection.
	 * If there are no more beans left in the collection, this method
	 * will return NULL.
	 *
	 * @return OODBBean|NULL
	 */
	public function next()
	{
		$row = $this->cursor->getNextItem();
		if ( $row ) {
			$beans = $this->repository->convertToBeans( $this->type, array( $row ), $this->mask );
			return reset( $beans );
		}
		return NULL;
	}

	/**
	 * Resets the collection from the start, like a fresh() on a bean.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->cursor->reset();
	}

	/**
	 * Closes the underlying cursor (needed for some databases).
	 *
	 * @return void
	 */
	public function close()
	{
		$this->cursor->close();
	}
}
}

namespace RedBeanPHP {

/**
 * QueryWriter
 * Interface for QueryWriters.
 * Describes the API for a QueryWriter.
 *
 * Terminology:
 *
 * - beautified property (a camelCased property, has to be converted first)
 * - beautified type (a camelCased type, has to be converted first)
 * - type (a bean type, corresponds directly to a table)
 * - property (a bean property, corresponds directly to a column)
 * - table (a checked and quoted type, ready for use in a query)
 * - column (a checked and quoted property, ready for use in query)
 * - tableNoQ (same as type, but in context of a database operation)
 * - columnNoQ (same as property, but in context of a database operation)
 *
 * @file    RedBeanPHP/QueryWriter.php
 * @author  Gabor de Mooij and the RedBeanPHP community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the RedBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface QueryWriter
{
	/**
	 * SQL filter constants
	 */
	const C_SQLFILTER_READ  = 'r';
	const C_SQLFILTER_WRITE = 'w';

	/**
	 * Query Writer constants.
	 */
	const C_SQLSTATE_NO_SUCH_TABLE                  = 1;
	const C_SQLSTATE_NO_SUCH_COLUMN                 = 2;
	const C_SQLSTATE_INTEGRITY_CONSTRAINT_VIOLATION = 3;
	const C_SQLSTATE_LOCK_TIMEOUT                   = 4;

	/**
	 * Define data type regions
	 *
	 * 00 - 80: normal data types
	 * 80 - 99: special data types, only scan/code if requested
	 * 99     : specified by user, don't change
	 */
	const C_DATATYPE_RANGE_SPECIAL   = 80;
	const C_DATATYPE_RANGE_SPECIFIED = 99;

	/**
	 * Define GLUE types for use with glueSQLCondition methods.
	 * Determines how to prefix a snippet of SQL before appending it
	 * to other SQL (or integrating it, mixing it otherwise).
	 *
	 * WHERE - glue as WHERE condition
	 * AND   - glue as AND condition
	 */
	const C_GLUE_WHERE = 1;
	const C_GLUE_AND   = 2;

	/**
	 * CTE Select Snippet
	 * Constants specifying select snippets for CTE queries
	 */
	 const C_CTE_SELECT_NORMAL = FALSE;
	 const C_CTE_SELECT_COUNT  = TRUE;

	/**
	 * Parses an sql string to create joins if needed.
	 *
	 * For instance with $type = 'book' and $sql = ' @joined.author.name LIKE ? OR @joined.detail.title LIKE ? '
	 * parseJoin will return the following SQL:
	 * ' LEFT JOIN `author` ON `author`.id = `book`.author_id
	 *   LEFT JOIN `detail` ON `detail`.id = `book`.detail_id
	 *   WHERE author.name LIKE ? OR detail.title LIKE ? '
	 *
	 * @note this feature requires Narrow Field Mode to be activated (default).
	 *
	 * @note A default implementation is available in AQueryWriter
	 * unless a database uses very different SQL this should suffice.
	 *
	 * @param string         $type the source type for the join
	 * @param string         $sql  the sql string to be parsed
	 *
	 * @return string
	 */
	public function parseJoin( $type, $sql );

	/**
	 * Writes an SQL Snippet for a JOIN, returns the
	 * SQL snippet string.
	 *
	 * @note A default implementation is available in AQueryWriter
	 * unless a database uses very different SQL this should suffice.
	 *
	 * @param string  $type         source type
	 * @param string  $targetType   target type (type to join)
	 * @param string  $leftRight    type of join (possible: 'LEFT', 'RIGHT' or 'INNER')
	 * @param string  $joinType     relation between joined tables (possible: 'parent', 'own', 'shared')
	 * @param boolean $firstOfChain is it the join of a chain (or the only join)
	 * @param string  $suffix       suffix to add for aliasing tables (for joining same table multiple times)
	 *
	 * @return string
	 */
	public function writeJoin( $type, $targetType, $leftRight, $joinType, $firstOfChain, $suffix );

	/**
	 * Glues an SQL snippet to the beginning of a WHERE clause.
	 * This ensures users don't have to add WHERE to their query snippets.
	 *
	 * The snippet gets prefixed with WHERE or AND
	 * if it starts with a condition.
	 *
	 * If the snippet does NOT start with a condition (or this function thinks so)
	 * the snippet is returned as-is.
	 *
	 * The GLUE type determines the prefix:
	 *
	 * * NONE  prefixes with WHERE
	 * * WHERE prefixes with WHERE and replaces AND if snippets starts with AND
	 * * AND   prefixes with AND
	 *
	 * This method will never replace WHERE with AND since a snippet should never
	 * begin with WHERE in the first place. OR is not supported.
	 *
	 * Only a limited set of clauses will be recognized as non-conditions.
	 * For instance beginning a snippet with complex statements like JOIN or UNION
	 * will not work. This is too complex for use in a snippet.
	 *
	 * @note A default implementation is available in AQueryWriter
	 * unless a database uses very different SQL this should suffice.
	 *
	 * @param string       $sql  SQL Snippet
	 * @param integer|NULL $glue the GLUE type - how to glue (C_GLUE_WHERE or C_GLUE_AND)
	 *
	 * @return string
	 */
	public function glueSQLCondition( $sql, $glue = NULL );

	/**
	 * Determines if there is a LIMIT 1 clause in the SQL.
	 * If not, it will add a LIMIT 1. (used for findOne).
	 *
	 * @note A default implementation is available in AQueryWriter
	 * unless a database uses very different SQL this should suffice.
	 *
	 * @param string $sql query to scan and adjust
	 *
	 * @return string
	 */
	public function glueLimitOne( $sql );

	/**
	 * Returns the tables that are in the database.
	 *
	 * @return array
	 */
	public function getTables();

	/**
	 * This method will create a table for the bean.
	 * This methods accepts a type and infers the corresponding table name.
	 *
	 * @param string $type type of bean you want to create a table for
	 *
	 * @return void
	 */
	public function createTable( $type );

	/**
	 * Returns an array containing all the columns of the specified type.
	 * The format of the return array looks like this:
	 * $field => $type where $field is the name of the column and $type
	 * is a database specific description of the datatype.
	 *
	 * This methods accepts a type and infers the corresponding table name.
	 *
	 * @param string $type type of bean you want to obtain a column list of
	 *
	 * @return array
	 */
	public function getColumns( $type );
