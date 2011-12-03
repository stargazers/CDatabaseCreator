<?php

/* 
CDatabaseCreator - Class for creating databases
Copyright (C) 2011 Aleksi Räsänen <aleksi.rasanen@runosydan.net>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
 
	// ************************************************** 
	//  CDatabaseCreator
	/*!
		@brief Class for creating databases and tables for it
		@author Aleksi Räsänen
		@email aleksi.rasanen@runosydan.net
		@copyright Aleksi Räsänen, 2011
		@license GNU AGPL
	*/
	// ************************************************** 
	class CDatabaseCreator
	{
		private $db;
		private $dbEngine;

		// ************************************************** 
		//  __construct
		/*!
			@brief Initialize class variables
		*/
		// ************************************************** 
		public function __construct()
		{
			$this->dbEngine = 'sqlite';
		}

		// ************************************************** 
		//  setDatabaseEngine
		/*!
			@brief Set database engine what we use. This can 
			  be 'SQLite' or 'MySQL'. Letter size does not matter.
			  This is used when we create queries to check if
			  tables already exists or not. If this method is
			  not called, then we use SQLite as a default.
			@param $db_engine Database engine
		*/
		// ************************************************** 
		public function setDatabaseEngine( $db_engine )
		{
			$possible = array( 'sqlite', 'mysql' );

			if( in_array( strtolower( $db_engine ), $possible ) )
				$this->dbEngine = strtolower( $db_engine );
		}

		// ************************************************** 
		//  getDatabseEngine
		/*!
			@brief Gets current database engine what is used
			@return String.
		*/
		// ************************************************** 
		public function getDatabseEngine()
		{
			return $this->dbEngine;
		}

		// ************************************************** 
		//  setDatabaseClass
		/*!
			@brief Sets a database class what we need to use
			@param $db Database class. This can be CSQLite or
			  CMySQL. NOTE! Database must be connected before
			  we use this class to create tables!
		*/
		// ************************************************** 
		public function setDatabaseClass( $db )
		{
			$this->db = $db;
		}

		// ************************************************** 
		//  createTable
		/*!
			@brief Creates a database table
			@param $table Database table name
			@param $fields Array where keys is field names and
			  values are types of fields.
		*/
		// ************************************************** 
		public function createTable( $table, $fields )
		{
			$q = $this->generateQuery( $table, $fields );

			// We get empty query string if database table already exists
			if( strlen( $q ) == 0 )
				return;

			$this->executeQuery( $q );
		}

		// ************************************************** 
		//  generateQuery
		/*!
			@brief Generate a query string for table creation
			@param $table Database table name
			@param $fields Array where keys is field names and
			  values are types of fields
			@return SQL Query string
		*/
		// ************************************************** 
		private function generateQuery( $table, $fields )
		{
			if( $this->doesTableExists( $table ) )
				return '';

			$q = 'CREATE TABLE ' . $table . ' ( ';

			$max = count( $fields );
			$i = 0;

			foreach( $fields as $name => $type )
			{
				$q .= $name . ' ' . $type;

				if( $i != $max -1 )
					$q .= ', ';

				$i++;
			}

			$q .= ' );';

			return $q;
		}

		// ************************************************** 
		//  doesTableExists
		/*!
			@brief Checks if database table exists
			@param $table Database table name
			@return True if table exists, false if not. 
		*/
		// ************************************************** 
		private function doesTableExists( $table )
		{
			if( $this->dbEngine == 'sqlite' )
			{
				$q = 'SELECT name FROM sqlite_master WHERE name="'
					. $table . '"';
			}
			else if( $this->dbEngine == 'mysql' )
			{
				$q = 'SELECT table_name FROM information_schema.tables '
					. 'WHERE TABLE_NAME="' . $table . '"';
			}

			$ret = $this->executeQuery( $q );

			if( $this->db->numRows( $ret ) > 0 )
				return true;

			return false;
		}

		// ************************************************** 
		//  executeQuery
		/*!
			@brief Executes a SQL Query in try-catch block
			@param $query SQL Query string
			@return Resultset if not failed. On fail we return NULL.
		*/
		// ************************************************** 
		private function executeQuery( $query )
		{
			try
			{
				return $this->db->query( $query );
			}
			catch( Exception $e )
			{
				return '';
			}
			
		}
	}

?>
