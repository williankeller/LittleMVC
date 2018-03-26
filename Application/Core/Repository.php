<?php

/**
 * Provides a simple interface to gather data from a database.
 *
 *
 * Copyright (C) 2018 MVC Framework.
 * This file included in MVC Framework is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Application\Core;

class Repository extends Config\Database
{

    /**
     * The primary key for the table.
     *
     * This can (and should) be overridden by the extending class.
     *
     * @access protected
     * @var    string
     */
    protected $_primaryKey = 'id';

    /**
     * Which columns we want to select.
     *
     * To mitigate SQL errors we always append the table name to the start of
     * the field name, whether or not one is supplied. If no table name is
     * passed in then we use the default table the the extended class declared.
     *
     * @access private
     * @var    array
     */
    private $_select = array();

    /**
     * The tables that we wish to select data from.
     *
     * @access private
     * @var    array
     */
    private $_from = array();

    /**
     * The clause conditions for where and having to apply to the query.
     *
     * @access private
     * @var    array
     */
    private $_clause = array();

    /**
     * The having conditions to apply.
     *
     * @access private
     * @var    array
     */
    private $_having = array();

    /**
     * How our queries should be grouped.
     *
     * @access private
     * @var    array
     */
    private $_group = array();

    /**
     * How we should order the returned rows.
     *
     * @access private
     * @var    array
     */
    private $_order = array();

    /**
     * How we should limit the returned rows.
     *
     * @access private
     * @var    array
     */
    private $_limit = array();

    /**
     * Data that has been passed to the row to insert/update.
     *
     * @access private
     * @var    array
     */
    private $_store = array();

    /**
     * Data that will be passed to the query.
     *
     * @access private
     * @var    array
     */
    private $_data;

    /**
     * Whether, after running a query, we should reset the model data.
     *
     * @access private
     * @var    boolean
     */
    private $_resetAfterQuery = true;

    /**
     * Setup the model.
     *
     * If you want to load a row automatically then you can pass an int to this
     * function, or to load multiple rows then you can pass an array or ints.
     *
     * @access public
     * @param  mixed  $id The ID to load automatically.
     */
    public function __construct($id = null)
    {
        if ($id) {
            $this->where($this->_primaryKey, '=', $id)
                ->limit(1)
                ->find();
            $this->_store = $this->fetch(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * Whether we should reset the query data after we have run the query.
     *
     * @access public
     * @param  boolean $reset
     * @return Model          For chainability.
     */
    public function setReset($reset = true)
    {
        $this->_resetAfterQuery = $reset;
        return $this;
    }

    /**
     * Add a row to the SELECT section.
     *
     * Note: The table name will always be prefixed to the field name to try and
     * mitigate errors . If none is supplied then we assume you are using the
     * table name that is declared in the extending class.
     *
     * @access public
     * @param  string $field The field name.
     * @param  string $as    The name of the field that is supplied to you.
     * @return Model         For chainability.
     */
    public function select($field, $as = null)
    {
        $this->_select[] = array('field' => $field, 'as' => $as);
        return $this;
    }

    /**
     * Add a table to the query.
     *
     * @access public
     * @param  string $table      A table that is part of the statement.
     * @param  string $joinType   How to join the table ('left', 'right', etc.).
     * @param  string $tableField First table join column.
     * @param  string $joinField  Second table join column.
     * @return Model
     */
    public function from($table, $joinType = null, $tableField = null, $joinField = null)
    {
        $this->_from[] = array(
            'table' => $table,
            'joinType' => $joinType,
            'tableField' => $tableField,
            'joinField' => $joinField
        );
        return $this;
    }

    /**
     * Add a where condition to the statement.
     *
     * @access public
     * @param  string       $field    The field we wish to test.
     * @param  string       $operator How we wish to test the field (=, >, etc.)
     * @param  string|array $value    The value to test the field against.
     * @param  string       $joiner   How to join the where clause to the next.
     * @return Model                  For chainability.
     */
    public function where($field, $operator, $value, $joiner = null)
    {
        $this->_clause[] = array(
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'joiner' => $joiner
        );
        return $this;
    }

    /**
     * Add a having condition to the statement.
     *
     * @access public
     * @param  string       $field    The field we wish to test.
     * @param  string       $operator How we wish to test the field (=, >, etc.)
     * @param  string|array $value    The value to test the field against.
     * @param  string       $joiner   How to join the where clause to the next.
     * @param  int          $brace    How many braces to open or close.
     * @return Model                  For chainability.
     */
    public function having($field, $operator, $value, $joiner = null, $brace = 0)
    {
        $this->_having[] = array(
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
            'joiner' => $joiner,
            'brace' => $brace
        );
        return $this;
    }

    /**
     * Group by a field.
     *
     * @access public
     * @param  string $field The field that we want to join on.
     * @return Model         For chainability.
     */
    public function group($field)
    {
        $this->_group[] = $field;
        return $this;
    }

    /**
     * Whether to open or close a brace.
     *
     * @access public
     * @param  string $status Either 'open' or 'close'.
     * @param  string $joiner Either 'AND' or 'OR'.
     * @return Model          For chainability.
     */
    public function brace($status, $joiner = null)
    {
        $this->_clause[] = ($status == 'open' ? '(' : ')') . ($joiner ? " {$joiner} " : '');
    }

    /**
     * How to order the returned results.
     *
     * @access public
     * @param  string $field     The field we wish to order by.
     * @param  string $direction Either 'ASC' or 'DESC'. 'ASC' by default.
     * @return Model             For chainability.
     */
    public function order($field, $direction = 'ASC')
    {
        $this->_order[] = array('field' => $field, 'direction' => $direction);
        return $this;
    }

    /**
     * How to limit the returned results.
     *
     * @access public
     * @param  int    $limit How many results to return.
     * @param  int    $start The offset to start the results from.
     * @return Model      For chainability.
     */
    public function limit($limit, $start = null)
    {
        $this->_limit = array('limit' => $limit, 'start' => $start);
        return $this;
    }

    /**
     * Insert a row into the table.
     *
     * @access public
     * @param  array  $data The data to insert into the table.
     */
    public function insert($data = array())
    {
        // If we have been supplied from data then add it to the store.
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }

        // If the insert was successful then add the primary key to the store
        if ($this->run($this->build('insert'), $this->_store, $this->_resetAfterQuery)) {
            $this->{$this->_primaryKey} = $this->_connection->lastInsertId();
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Select some records from a table.
     *
     * @access public
     */
    public function find()
    {
        $this->run($this->build('select'), $this->_data, $this->_resetAfterQuery);
    }

    /**
     * Update a row in the table.
     *
     * @access public
     * @param  array  $data The data to update the table with.
     */
    public function update($data = array())
    {
        // If we have been supplied from data then add it to the store.
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }

        // If the where clause is empty then assume we are updating via primary key
        if (!$this->_clause) {
            $this->where($this->_primaryKey, '=', $this->{$this->_primaryKey});
        }
        // If the insert was successful then add the primary key to the store
        $this->run($this->build('update'), $this->_data, $this->_resetAfterQuery);
    }

    /**
     * Shorthand for the insert and update functions.
     *
     * @access public
     * @param  array  $data The data to insert or update.
     */
    public function save($data = array())
    {
        $id = $this->{$this->_primaryKey};
        return ($id) ? $this->update($data) : $this->insert($data);
    }

    /**
     * Delete rows from the table.
     *
     * @access public
     * @param  int    $id The ID of the row we wish to delete.
     */
    public function delete($id = null)
    {
        // Is there an ID that we need to delete?
        if ($id) {
            $this->where($this->_primaryKey, '=', $id);
        }
        $this->run($this->build('delete'), $this->_data, $this->_resetAfterQuery);
    }

    /**
     * Piece together all of the sections of the query.
     *
     * @access public
     * @param  string $type What type of query we wish to build.
     * @return string       The SQL that has been generated.
     */
    public function build($type)
    {
        switch ($type) {
            case 'insert' : $sql = $this->buildInsert();
                break;
            case 'select' : $sql = $this->buildSelect();
                break;
            case 'update' : $sql = $this->buildUpdate();
                break;
            case 'delete' : $sql = $this->buildDelete();
                break;
        }
        return $sql;
    }

    /**
     * Build an insert statement.
     *
     * @access private
     * @return string
     */
    private function buildInsert()
    {
        $keys   = array_keys($this->_store);
        $fields = implode(', ', $keys);
        $values = implode(', :', $keys);

        return "INSERT INTO {$this->buildFragmentFrom()} ({$fields}) VALUES (:{$values})";
    }

    /**
     * Build a select statement.
     *
     * @access private
     * @return string
     */
    private function buildSelect()
    {
        return "SELECT {$this->buildFragmentSelect()}
                FROM {$this->buildFragmentFrom()}
                     {$this->buildFragmentWhere()}
                     {$this->buildFragmentWhere('HAVING')}
                     {$this->buildFragmentGroup()}
                     {$this->buildFragmentOrder()}
                     {$this->buildFragmentLimit()}";
    }

    /**
     * Build an update statement.
     *
     * @access private
     * @return string
     */
    private function buildUpdate()
    {
        return "UPDATE {$this->buildFragmentFrom()}
                SET {$this->buildFragmentUpdate()}
                    {$this->buildFragmentWhere()}
                    {$this->buildFragmentLimit()}";
    }

    /**
     * Build a delete statement.
     *
     * @access private
     * @return string
     */
    private function buildDelete()
    {
        return "DELETE FROM {$this->buildFragmentFrom()}
		            {$this->buildFragmentWhere()}
		            {$this->buildFragmentLimit()}";
    }

    /**
     * Build the SELECT portion of the statement.
     *
     * @access private
     * @return string
     */
    private function buildFragmentSelect()
    {
        // If there are no fields to select from then just return them all
        if (empty($this->_select)) {
            return '*';
        }

        // Container for the fields we wish to select
        $fields = array();

        // Loop over each field that we want to return and build its SQL
        foreach ($this->_select as $select) {
            $as = $select['as'] ? " AS '{$select['as']}'" : '';

            $fields[] = "{$select['field']} {$as}";
        }
        return implode(', ', $fields);
    }

    /**
     * Build the FROM portion of the statement.
     *
     * @access private
     * @return string
     */
    private function buildFragmentFrom()
    {
        // If there are no fields to select from then just return them all
        if (empty($this->_from)) {
            return $this->_table;
        }

        // Container for the tables we wish to use
        $tables = array();

        // Loop over each table and build its SQL
        foreach ($this->_from as $from) {
            $tables[] = $from['tableField'] && $from['joinField'] ? "{$from['joinType']} JOIN {$from['table']} ON {$from['tableField']} = {$from['joinField']}" : $from['table'];
        }
        return implode(' ', $tables);
    }

    /**
     * Build the SET portion of the statement.
     *
     * @access private
     * @return string
     */
    private function buildFragmentUpdate()
    {
        // Container for the fields that will be updated
        $fields = array();

        foreach ($this->_store as $field => $value) {
            // We do not want to update the primary key
            if ($field == $this->_primaryKey) {
                continue;
            }

            $fields[] = "{$field} = :{$field}";
         
            $this->_data[$field] = $value;
        }
        return implode(', ', $fields);
    }

    /**
     * Build the WHERE portion of the statement.
     *
     * Note: So we do not interfere with any field names we label our prepared
     * variables prefixed with "__where_".
     *
     * @access private
     * @param  string  $type Whether this is a WHERE or HAVING clause.
     * @return string
     * @todo   Allow for OR's.
     */
    private function buildFragmentWhere($type = 'WHERE')
    {
        // If there are no conditions then return nothing
        if ($type == 'HAVING' && empty($this->_having)) {
            return '';
        }
        else if (empty($this->_clause)) {
            return '';
        }

        // Container for the where conditions
        $sql        = '';
        $sqlClauses = '';
        $clauses    = $type == 'HAVING' ? $this->_having : $this->_clause;
        $clauseType = strtolower($type);

        // Loop over each where condition and build its SQL
        foreach ($clauses as $clauseIndex => $clause) {
            // Are we opening or closing a brace?
            if (!is_array($clause)) {
                $sqlClauses .= $clause;
                continue;
            }

            // The basic perpared variable name
            $clauseVar = "__{$clauseType}_{$clauseIndex}";

            // Reset the SQL for this single clause
            $sql = '';

            // We are dealing with an IN
            if (is_array($clause['value'])) {
                // We need to create the condition as :a, :b, :c
                $clauseIn = array();

                // Loop over each value in the array
                foreach ($clause['value'] as $index => $value) {
                    $clauseIn[] = ":{$clauseVar}_{$index}";

                    $this->_data["{$clauseVar}_{$index}"] = $value;
                }

                // The SQL for this IN
                $sql .= "{$clause['field']} IN (" . implode(', ', $clauseIn) . ")";
            }

            // A simple where condition
            else {
                $sql .= "{$clause['field']} {$clause['operator']} :{$clauseVar}";

                $this->_data[$clauseVar] = $clause['value'];
            }

            // Add any joiner (AND, OR< etc) that the user has added
            $sql .= $clause['joiner'] ? " {$clause['joiner']} " : '';

            // And add to the where clause
            $sqlClauses .= $sql;
        }

        return "{$type} {$sqlClauses}";
    }

    /**
     * Build the GROUP BY portion of the statement.
     *
     * @access private
     * @return string
     */
    private function buildFragmentGroup()
    {
        return !empty($this->_group) ? 'GROUP BY ' . implode(', ', $this->_group) : '';
    }

    /**
     * Build the ORDER BY portion of the statement.
     *
     * @access private
     * @return string
     */
    private function buildFragmentOrder()
    {
        // If there are no order by's then return nothing
        if (empty($this->_order)) {
            return '';
        }

        // Container for the order by's
        $orders = array();

        // Loop over each order by and build its SQL
        foreach ($this->_order as $order) {
            $orders[] = "{$order['field']} {$order['direction']}";
        }

        return 'ORDER BY ' . implode(', ', $orders);
    }

    /**
     * Build the LIMIT portion of the statement.
     *
     * @access private
     * @return string
     */
    private function buildFragmentLimit()
    {
        // If there is no limit then return nothing
        if (empty($this->_limit)) {
            return '';
        }

        // If there is a start then add that as well...
        if (!is_null($this->_limit['start'])) {
            return "LIMIT {$this->_limit['start']}, {$this->_limit['limit']}";
        }

        // ... otherwise just return the simple limit
        return "LIMIT {$this->_limit['limit']}";
    }

    /**
     * Get how many rows the statement located.
     *
     * @access public
     * @return int|boolean int if statement was successful, boolean false otherwise.
     */
    public function rowCount()
    {
        return $this->_statement ? $this->_statement->rowCount() : false;
    }

    /**
     * Get the next row of the located results.
     *
     * @access public
     * @param  \PDO   $method In what format the dataset should be returned.
     * @return mixed          Array if statement was successful, boolean false otherwise.
     */
    public function fetch($method = \PDO::FETCH_OBJ)
    {
        return $this->_statement ? $this->_statement->fetch($method) : false;
    }

    /**
     * Get the next row of the located results.
     *
     * @access public
     * @param  \PDO   $method In what format the dataset should be returned.
     * @return mixed          Array if statement was successful, boolean false otherwise.
     */
    public function fetchAll($method = \PDO::FETCH_OBJ)
    {
        return $this->_statement ? $this->_statement->fetchAll($method) : false;
    }

    /**
     * Reset the query ready for the next one to avoid contamination.
     *
     * Note: This function is called everytime we have run a query automatically.
     *
     * @access public
     */
    public function reset()
    {
        $this->_select = array();
        $this->_from   = array();
        $this->_clause = array();
        $this->_having = array();
        $this->_group  = array();
        $this->_order  = array();
        $this->_limit  = array();
        $this->_data   = array();
        $this->_store  = array();
    }

    /**
     * Set a variable for the row.
     *
     * Note: This is only used for inserting and updating statements. It will
     * also update any previous value the field had.
     *
     * @access public
     * @param  string $variable The field to manipulate.
     * @param  mixed  $value    The field's value.
     * @magic
     */
    public function __set($variable, $value)
    {
        $this->_store[$variable] = $value;
    }

    /**
     * Get a field value.
     *
     * Note: This is only used for inserting and updating statements. For all
     * other statements you can use the fetch() function.
     *
     * @access public
     * @param  string         $field The name of the field.
     * @return string|boolean        String if exists, boolean false otherwise.
     */
    public function __get($field)
    {
        return isset($this->_store[$field]) ? $this->_store[$field] : false;
    }

}
