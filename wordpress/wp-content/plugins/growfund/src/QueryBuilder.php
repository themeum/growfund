<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\QueryException;
use Exception;
use wpdb;

/**
 * Class QueryBuilder
 *
 * A lightweight query builder for interacting with WordPress's $wpdb.
 */
class QueryBuilder
{
    /**
     * @var wpdb
     */
    protected $db;

    /**
     * @var string
     */
    protected $query = '';

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $select_clauses = ['*'];
    protected $join_clauses = [];
    protected $set_clauses = [];
    protected $where_clauses = [];
    protected $where_between_clauses = [];
    protected $or_where_clauses = [];
    protected $order_by_clauses = [];
    protected $group_by_clauses = [];
    protected $having_clauses = [];
    protected $or_having_clauses = [];
    protected $delete_clauses = null;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int|null
     */
    protected $offset;

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * @return wpdb
     *  
     */
    public static function get_db()
    {
        global $wpdb;

        return $wpdb;
    }

    /**
     * Wrap a value in single quotes.
     *
     * @param mixed $value
     * @return string
     */
    public static function quote($value)
    {
        global $wpdb;
        return $wpdb->prepare('%s', $value);
    }

    /**
     * Create a new instance.
     *
     * @return static
     */
    public static function query()
    {
        return new static();
    }

    /**
     * Begin a transaction.
     *
     * Starts a transaction, which can then be committed or rolled back.
     *
     * @return void
     */
    public static function begin_transaction()
    {
        static::query()->db->query('START TRANSACTION');
    }

    /**
     * Commit the current transaction.
     *
     * Commits the current transaction, writing all the changes to the database.
     *
     * @return void
     */
    public static function commit()
    {
        static::query()->db->query('COMMIT');
    }

    /**
     * Roll back the current transaction.
     *
     * Reverts the changes made during the current transaction, effectively
     * undoing all of them.
     *
     * @return void
     */
    public static function rollback()
    {
        static::query()->db->query('ROLLBACK');
    }

    /**
     * Add a prefix to a table name.
     *
     * @return string
     */
    public static function prefix(string $table_name)
    {
        global $wpdb;

        return $wpdb->prefix . $table_name;
    }

    /**
     * Run a raw SQL query (Must contain only named placeholders).
     *
     * @param string $sql
     * @param array $bindings
     * @return mixed
     * @throws Exception
     */
    public static function raw(string $sql, array $bindings = [])
    {
        if (empty($sql)) {
            throw new QueryException(esc_html__('Raw SQL query cannot be empty', 'growfund'));
        }

        $instance = new static();

        $prepared_sql = $instance->prepare($sql, $bindings);

        // Decide type of query by inspecting it
        $command = strtoupper(explode(' ', trim($sql), 2)[0]);

        switch ($command) {
            case 'SELECT':
                $result = $instance->db->get_results($prepared_sql);
                break;
            case 'INSERT':
            case 'UPDATE':
            case 'DELETE':
            default:
                $result = $instance->db->query($prepared_sql);
                break;
        }

        $instance->check_error();

        return $result;
    }


    /**
     * Set the table for the query.
     *
     * @param string $table
     * @param bool $is_prefixed
     * @return $this
     */
    public function table(string $table, bool $is_prefixed = false)
    {
        $this->table = !$is_prefixed ? $this->db->prefix . $table : $table;

        return $this;
    }

    /**
     * Get the table name for the query.
     * @return string
     */
    public function get_table_name()
    {
        return $this->table;
    }

    /**
     * Set selected columns.
     *
     * @param array $columns
     * @return $this
     */
    public function select(array $columns = ['*'])
    {
        if (count($columns) === 1 && $columns[0] === '*') {
            $this->select_clauses = $columns;

            return $this;
        }

        if (count($this->select_clauses) === 1 && $this->select_clauses[0] === '*') {
            $this->select_clauses = $columns;

            return $this;
        }


        $this->select_clauses = array_merge($this->select_clauses, $columns);

        return $this;
    }

    /**
     * Add a JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $second
     * @param string $type
     * @return $this
     */
    public function join(string $table, string $first, string $second, string $type = 'INNER')
    {
        $table = $this->db->prefix . $table;
        $this->join_clauses[] = strtoupper($type) . ' JOIN ' . $table . ' ON ' . $first . ' = ' . $second;

        return $this;
    }

    /**
     * Add a JOIN clause using raw SQL condition.
     *
     * @param string $table Table name
     * @param string $type Join type (INNER, LEFT, RIGHT, CROSS)
     * @param string $raw_condition Raw SQL condition
     *
     * @return $this
     */
    public function join_raw(string $table, string $type, string $raw_condition)
    {
        $table = $this->db->prefix . $table;
        $this->join_clauses[] = strtoupper($type) . ' JOIN ' . $table . ' ON ' . $raw_condition;

        return $this;
    }

    /**
     * Add Inner Join
     *
     * @param string $table
     * @param string $first
     * @param string $second
     * @return $this
     */
    public function inner_join(string $table, string $first, string $second)
    {
        return $this->join($table, $first, $second, 'INNER');
    }

    /**
     * Add Left Join
     *
     * @param string $table
     * @param string $first
     * @param string $second
     * @return $this
     */
    public function left_join(string $table, string $first, string $second)
    {
        return $this->join($table, $first, $second, 'LEFT');
    }

    /**
     * Add Right Join
     *
     * @param string $table
     * @param string $first
     * @param string $second
     * @return $this
     */
    public function right_join(string $table, string $first, string $second)
    {
        return $this->join($table, $first, $second, 'RIGHT');
    }


    /**
     * Add a WHERE condition.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed|null $value
     * @return $this
     */
    public function where(string $column, $operator, $value = null)
    {
        if (count(func_get_args()) === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->where_clauses[] = [$column, $operator, $value];

        return $this;
    }

    /**
     * Add a WHERE raw condition.
     *
     * @param string $raw
     * @return $this
     */
    public function where_raw(string $raw, array $bindings = [])
    {
        $this->where_clauses[] = $this->prepare($raw, $bindings);

        return $this;
    }

    /**
     * Add a WHERE date condition.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed|null $value
     * @return $this
     */
    public function where_date(string $column, $operator, $value = null)
    {
        if (count(func_get_args()) === 2) {
            $value = $operator;
            $operator = '=';
        }

        $protected_column = $this->protect_identifier($column);

        return $this->where_raw("DATE({$protected_column}) {$operator} :val", ['val' => $value]);
    }

    /**
     * Add a AND WHERE IN condition.
     *
     * @param string $column
     * @param array $value
     * @return $this
     */
    public function where_in(string $column, array $value)
    {
        return $this->where($column, 'IN', $value);
    }

    /**
     * Add a AND WHERE NOT IN condition.
     *
     * @param string $column
     * @param array $value
     * @return $this
     */
    public function where_not_in(string $column, array $value)
    {
        return $this->where($column, 'NOT IN', $value);
    }

    /* Add an AND WHERE BETWEEN condition.
     * @param string $column
     * @param mixed $start
     * @param mixed $end
     * 
     * @return static
     */
    public function where_between(string $column, $start, $end)
    {
        $this->where_between_clauses[] = [$column, $start, $end];

        return $this;
    }

    /**
     * Add an OR WHERE condition.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed|null $value
     * @return $this
     */
    public function or_where(string $column, $operator, $value = null)
    {
        if (count(func_get_args()) === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->or_where_clauses[] = [$column, $operator, $value];

        return $this;
    }

    /**
     * Add GROUP BY clause.
     *
     * @param string|array $columns
     * @return $this
     */
    public function group_by($columns)
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        $this->group_by_clauses = $columns;

        return $this;
    }

    /**
     * Add HAVING clause.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed|null $value
     * @return $this
     */
    public function having(string $column, $operator, $value = null)
    {
        if (count(func_get_args()) === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->having_clauses[] = [$column, $operator, $value];

        return $this;
    }

    /**
     * Add OR HAVING clause.
     *
     * @param string $column
     * @param mixed $operator
     * @param mixed|null $value
     * @return $this
     */
    public function or_having(string $column, $operator, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->or_having_clauses[] = [$column, $operator, $value];

        return $this;
    }



    /**
     * Add ORDER BY clause.
     *
     * @param string $column
     * @param string $order
     * @return $this
     */
    public function order_by(string $column, string $order = 'DESC')
    {
        $this->order_by_clauses[] = [$column, $order];

        return $this;
    }

    /**
     * Set query limit.
     *
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set query offset.
     *
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Get the prepared SQL query.
     *
     * @return string
     */
    public function to_sql()
    {
        return $this->db->prepare($this->build_query(), $this->bindings);
    }

    /**
     * Get the first result row.
     *
     * @return object|null
     * @throws QueryException
     */
    public function first()
    {
        $sql = $this->db->prepare($this->build_query(), $this->bindings);
        $result = $this->db->get_row($sql);

        $this->check_error();

        return $result;
    }

    /**
     * Find by primary key (assumes `ID` column).
     *
     * @param mixed $id
     * @return object|null
     * @throws QueryException
     */
    public function find($id, $columns = 'ID')
    {
        return $this->where($columns, $id)->first();
    }

    /**
     * Get all matching results.
     *
     * @return array
     * @throws QueryException
     */
    public function get()
    {
        $sql = $this->db->prepare($this->build_query(), $this->bindings);
        $result = $this->db->get_results($sql);

        $this->check_error();

        return $result ?? [];
    }

    /**
     * Alias for get().
     *
     * @return array
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Paginate results.
     *
     * @param int $page
     * @param int $limit
     * @param array<int,array<int,array{key:string,operator:string|mixed,value:mixed|null}>> $overall_query_conditions 
     *      - List of conditions, grouped in nested arrays. Each condition must contain 'key', 'operator', and 'value'. 'value' is optional.
     * @return array
     */
    public function paginate($page = 1, int $limit = 20, array $overall_query_conditions = [])
    {
        if ($limit < 1) {
            throw new Exception(esc_html__('Limit cannot be less than 1', 'growfund'));
        }

        $data = [];
        $query_obj = clone $this;
        $total_count = $query_obj->count();
        $overall = $this->overall('*', $overall_query_conditions);

        $page = max(1, (int) $page);
        $offset = max(0, ((int) $page - 1) * $limit);
        $data['results'] = $this->limit($limit)->offset($offset)->get();

        $data['count'] = count($data['results']);
        $data['total'] = $total_count;
        $data['current_page'] = $page;
        $data['has_more'] = (int) ceil($total_count / $limit) > $page;
        $data['per_page'] = $limit;
        $data['overall'] = $overall;

        return $data;
    }

    /**
     * Count the total matching records.
     *
     * @return int
     * @throws QueryException
     */
    public function count(string $column = '*')
    {
        $this->select(["COUNT({$column}) as growfund_total_count_value"]);
        $sql = $this->db->prepare($this->build_query(), $this->bindings);
        $row = $this->db->get_row($sql);

        $this->check_error();

        return intval($row->growfund_total_count_value ?? 0);
    }

    /**
     * Clear the clauses.
     *
     * @param string|array $clauses
     * @return static
     */
    public function clear($clauses)
    {
        $available_clauses = [
            'select',
            'where',
            'where_between',
            'or_where',
            'group_by',
            'having',
            'or_having',
            'limit',
            'offset',
        ];

        if (!is_array($clauses)) {
            $clauses = [$clauses];
        }

        $invalid_clauses = array_diff($clauses, $available_clauses);

        if (!empty($invalid_clauses)) {
            throw new Exception(
                sprintf(
                    /* translators: %s: Invalid clauses */
                    esc_html__('Invalid clauses provided: %s', 'growfund'),
                    esc_html(implode(', ', $invalid_clauses))
                )
            );
        }

        foreach ($clauses as $clause) {
            $clear_method = 'clear_' . strtolower($clause);

            if (!method_exists($this, $clear_method)) {
                throw new Exception(
                    sprintf(
                        /* translators: %s: Clear method name */
                        esc_html__('Invalid clear method %s provided.', 'growfund'),
                        esc_html($clear_method)
                    )
                );
            }

            $this->$clear_method();
        }

        return $this;
    }

    /**
     * Clear the SELECT clauses.
     *
     * @return static
     */
    protected function clear_select()
    {
        $this->select_clauses = ['*'];
        return $this;
    }

    /**
     * Clear the WHERE clauses.
     *
     * @return static
     */
    protected function clear_where()
    {
        $this->where_clauses = [];
        return $this;
    }

    /**
     * Clear the WHERE BETWEEN clauses.
     *
     * @return static
     */
    protected function clear_where_between()
    {
        $this->where_between_clauses = [];
        return $this;
    }

    /**
     * Clear the OR WHERE clauses.
     *
     * @return static
     */
    protected function clear_or_where()
    {
        $this->or_where_clauses = [];
        return $this;
    }

    /**
     * Clear the GROUP BY clauses.
     *
     * @return static
     */
    protected function clear_group_by()
    {
        $this->group_by_clauses = [];
        return $this;
    }

    /**
     * Clear the HAVING clauses.
     *
     * @return static
     */
    protected function clear_having()
    {
        $this->having_clauses = [];
        return $this;
    }

    /**
     * Clear the OR HAVING clauses.
     *
     * @return static
     */
    protected function clear_or_having()
    {
        $this->or_having_clauses = [];
        return $this;
    }

    /**
     * Clear the limit of the query
     *
     * @return static
     */
    protected function clear_limit()
    {
        $this->limit = null;

        return $this;
    }

    /**
     * Clear the offset of the query
     *
     * @return static
     */
    protected function clear_offset()
    {
        $this->offset = null;

        return $this;
    }

    /**
     * Get the overall count of the query.
     *
     * @param string $column
     * * @param array<int,array<int,array{key:string,operator:string|mixed,value:mixed|null}>> $overall_query_conditions 
     *      - List of conditions, grouped in nested arrays. Each condition must contain 'key', 'operator', and 'value'. 'value' is optional.
     * @return int
     */
    public function overall(string $column = '*', array $overall_query_conditions = [])
    {
        $cloned = clone $this;
        $cloned->clear([
            'select',
            'where',
            'where_between',
            'or_where',
            'group_by',
            'having',
            'or_having',
            'limit',
            'offset'
        ]);

        $cloned->select(["COUNT({$column}) as growfund_overall_count_value"]);

        foreach ($overall_query_conditions as $condition) {
            if (empty($condition)) {
                continue;
            }

            $count = count($condition);

            if ($count < 2 && $count > 3) {
                throw new Exception(esc_html__('Invalid overall condition provided.', 'growfund'));
            }

            if ($count === 2) {
                $cloned->where($condition[0], $condition[1]);
                continue;
            }

            $cloned->where($condition[0], $condition[1], $condition[2]);
        }

        $sql = $cloned->db->prepare($cloned->build_query(), $cloned->bindings);
        $row = $cloned->db->get_row($sql);

        return intval($row->growfund_overall_count_value ?? 0);
    }

    /**
     * Sum a numeric column.
     *
     * @param string $column
     * @return float|int
     * @throws QueryException
     */
    public function sum(string $column)
    {
        $this->select(['SUM(' . $column . ')']);
        $sql = $this->db->prepare($this->build_query(), $this->bindings);

        $sum = $this->db->get_var($sql);

        $this->check_error();

        if (empty($sum)) {
            return 0;
        }

        if (filter_var($sum, FILTER_VALIDATE_INT) !== false) {
            return (int) $sum;
        }

        return (float) $sum;
    }

    /**
     * Average a numeric column.
     *
     * @param string $column
     * @return float|int
     * @throws QueryException
     */
    public function avg(string $column)
    {
        $this->select(['AVG(' . $column . ')']);
        $sql = $this->db->prepare($this->build_query(), $this->bindings);

        $avg = $this->db->get_var($sql);

        $this->check_error();

        if (empty($avg)) {
            return 0;
        }

        if (filter_var($avg, FILTER_VALIDATE_INT) !== false) {
            return (int) $avg;
        }

        return (float) $avg;
    }

    /**
     * Max value of a column.
     *
     * @param string $column
     * @return string|null
     */
    public function max(string $column)
    {
        $this->select(['MAX(' . $column . ')']);
        $sql = $this->db->prepare($this->build_query(), $this->bindings);

        return $this->db->get_var($sql);
    }

    /**
     * Min value of a column.
     *
     * @param string $column
     * @return string|null
     */
    public function min(string $column)
    {
        $this->select(['MIN(' . $column . ')']);
        $sql = $this->db->prepare($this->build_query(), $this->bindings);

        return $this->db->get_var($sql);
    }

    /**
     * Insert a new record.
     *
     * @param array $data
     * @return int|false
     * @throws QueryException
     */
    public function create(array $data)
    {
        $formats = array_map(function ($value) {
            return $this->get_placeholder_by_value($value);
        }, array_values($data));

        $id = $this->db->insert($this->table, $data, $formats)
            ? $this->db->insert_id
            : false;

        $this->check_error();

        return $id;
    }

    /**
     * Insert a multiple records.
     *
     * @param array $data
     * @return bool
     * @throws QueryException
     */
    public function insert(array $data)
    {
        if (empty($data) || !isset($data[0])) {
            throw new QueryException(esc_html__('Data for insert cannot be empty', 'growfund'));
        }

        $columns = array_keys($data[0]);
        $protected_columns = array_map([$this, 'protect_identifier'], $columns);

        $value_sets = [];
        $bindings = [];

        foreach ($data as $data_item) {
            $placeholders = [];

            foreach ($data_item as $key => $value) {
                if (is_null($value)) {
                    $placeholders[] = 'NULL'; // No placeholder
                } else {
                    $placeholders[] = $this->get_placeholder_by_value($value);
                    $bindings[] = $value;
                }
            }

            $value_sets[] = '(' . implode(',', $placeholders) . ')';
        }

        $sql = 'INSERT INTO ' . $this->table . ' (' . implode(',', $protected_columns) . ') VALUES ' . implode(',', $value_sets);

        $prepared_sql = $this->db->prepare($sql, $bindings);

        $is_success = $this->db->query($prepared_sql);

        $this->check_error();

        return $is_success;
    }

    /**
     * Update existing record(s).
     *
     * @param array $data
     * @return int|false Number of rows affected or false on failure
     * @throws QueryException
     */
    public function update(array $data)
    {
        $this->set_clauses = $data;

        $sql = $this->db->prepare($this->build_query('update'), $this->bindings);
        $result = $this->db->query($sql);

        $this->check_error();

        return $result;
    }

    /**
     * Delete matching records.
     *
     * @param mixed $tables
     * @return int|false Number of rows deleted or false on failure
     * @throws QueryException
     */
    public function delete($tables = null)
    {
        $this->delete_clauses = $tables;
        $sql = $this->db->prepare($this->build_query('delete'), $this->bindings);
        $result = $this->db->query($sql);

        $this->check_error();

        return $result;
    }


    /**
     * Protect an identifier (column or table name) by wrapping it in backticks.
     * Handles table.column format.
     *
     * @param string $identifier
     * @return string
     */
    protected function protect_identifier(string $identifier)
    {
        if ($identifier === '*') {
            return '*';
        }

        if (preg_match('/^([A-Za-z_]+)\((.+)\)$/i', trim($identifier), $matches)) {
            $function = $matches[1];
            $inner    = $matches[2];
            
            if ($inner === '*') {
                return "{$function}(*)";
            }

            $innerParts = explode('.', $inner);
            $innerParts = array_map(function($part){
                    return '`' . str_replace('`', '', trim($part)) . '`';
			}, $innerParts);

            return "{$function}(" . implode('.', $innerParts) . ")";
        }

        $parts = explode('.', $identifier);
        $parts = array_map(function($part) {
            return '`' . str_replace('`', '', trim($part)) . '`';
        }, $parts);

        return implode('.', $parts);
    }

    /**
     * Build SQL query string.
     *
     * @param string $type
     * @return string
     */
    public function build_query(string $type = 'select')
    {
        if (empty($this->table)) {
            throw new Exception(esc_html__('Table name is required', 'growfund'));
        }

        switch ($type) {
            case 'select':
                $this->query = 'SELECT ' . implode(', ', $this->select_clauses) . ' FROM ' . $this->table;

                if (!empty($this->join_clauses)) {
                    $this->query .= ' ' . implode(' ', $this->join_clauses);
                }

                break;

            case 'update':
                $this->query = 'UPDATE ' . $this->table;

                if (!empty($this->join_clauses)) {
                    $this->query .= ' ' . implode(' ', $this->join_clauses);
                }

                $this->query .= ' SET ';

                foreach ($this->set_clauses as $key => $value) {
                    $this->query .= $this->protect_identifier($key) . ' = ' . $this->get_placeholder_by_value($value) . ', ';
                    $this->add_binding($value);
                }

                $this->query = rtrim($this->query, ', ');
                break;

            case 'delete':
                if (!empty($this->delete_clauses)) {
                    $delete_clauses = is_array($this->delete_clauses)
                        ? implode(',', $this->delete_clauses)
                        : $this->delete_clauses;

                    $this->query = 'DELETE ' . $delete_clauses . ' FROM ' . $this->table;
                } else {
                    $this->query = 'DELETE FROM ' . $this->table;
                }

                if (!empty($this->join_clauses)) {
                    $this->query .= ' ' . implode(' ', $this->join_clauses);
                }

                break;

            default:
                break;
        }


        if ($this->has_where_clause() || $this->has_or_where_clause() || $this->has_where_between_clause()) {
            $this->query .= ' WHERE';
        }

        if ($this->has_where_clause()) {
            $where_clause = [];

            foreach ($this->where_clauses as $where) {
                if (is_string($where)) {
                    if (strpos($where, 'LIKE') !== false) {
                        $value = str_replace('%', '%%', $where);
                    }

                    $where_clause[] = $where;
                } elseif (($where[1] === 'IN' || $where[1] === 'NOT IN') && is_array($where[2])) {
                    if (empty($where[2])) {
                        $where_clause[] = $where[1] === 'IN' ? " 0=1 " : " 1=1 ";
                        continue;
                    }

                    $placeholders = [];

                    foreach ($where[2] as $item) {
                        $placeholders[] = $this->get_placeholder_by_value($item);
                        $this->add_binding($item);
                    }

                    $where_clause[] = implode(' ', [$this->protect_identifier($where[0]), $where[1], '(' . implode(',', $placeholders) . ')']);
                } elseif ($where[1] === 'LIKE') {
                    $value = str_replace('%', '%%', $where[2]);
                    $where_clause[] = implode(' ', [$this->protect_identifier($where[0]), $where[1],  $this->get_placeholder_by_value($value)]);
                    $this->add_binding($value);
                } else {
                    $where_clause[] = implode(' ', [$this->protect_identifier($where[0]), $where[1], $this->get_placeholder_by_value($where[2])]);
                    $this->add_binding($where[2]);
                }
            }

            $this->query .= ' ' . implode(' AND ', $where_clause);
        }

        if ($this->has_where_between_clause()) {
            $where_between_clause = [];

            foreach ($this->where_between_clauses as $where) {
                $where_between_clause[] = implode(' ', ['(', $this->protect_identifier($where[0]), 'BETWEEN', $this->get_placeholder_by_value($where[1]), 'AND', $this->get_placeholder_by_value($where[2]), ')']);
                $this->add_binding($where[1]);
                $this->add_binding($where[2]);
            }

            if ($this->has_where_clause()) {
                $this->query .= ' AND';
            }

            $this->query .= ' ' . implode(' AND ', $where_between_clause);
        }

        if ($this->has_or_where_clause()) {
            $or_where_clause = [];

            foreach ($this->or_where_clauses as $orWhere) {
                $or_where_clause[] = implode(' ', [$this->protect_identifier($orWhere[0]), $orWhere[1], $this->get_placeholder_by_value($orWhere[2])]);
                $this->add_binding($orWhere[2]);
            }

            $this->query .= ' OR ' . implode(' OR ', $or_where_clause);
        }

        if (!empty($this->group_by_clauses)) {
            $group_by_clauses = array_map([$this, 'protect_identifier'], $this->group_by_clauses);
            $this->query .= ' GROUP BY ' . implode(', ', $group_by_clauses);
        }

        if (!empty($this->having_clauses) || !empty($this->or_having_clauses)) {
            $this->query .= ' HAVING';
        }

        if (!empty($this->having_clauses)) {
            $having_clauses = [];
            foreach ($this->having_clauses as $having) {
                $having_clauses[] = implode(' ', [$this->protect_identifier($having[0]), $having[1], $this->get_placeholder_by_value($having[2])]);
                $this->add_binding($having[2]);
            }

            $this->query .= ' ' . implode(' AND ', $having_clauses);
        }

        if (!empty($this->or_having_clauses)) {
            $or_having_clauses = [];
            foreach ($this->or_having_clauses as $or_having) {
                $or_having_clauses[] = implode(' ', [$this->protect_identifier($or_having[0]), $or_having[1], $this->get_placeholder_by_value($or_having[2])]);
                $this->add_binding($or_having[2]);
            }

            $this->query .= ' OR ' . implode(' OR ', $or_having_clauses);
        }


        if (!empty($this->order_by_clauses)) {
            $order_by_clause = [];

            foreach ($this->order_by_clauses as $orderBy) {
                $column = $this->protect_identifier($orderBy[0]);
                $direction = strtoupper($orderBy[1]);

                if (!in_array($direction, ['ASC', 'DESC'], true)) {
                    $direction = 'ASC';
                }

                $order_by = sanitize_sql_orderby("$column $direction");

                if ($order_by) {
                    $order_by_clause[] = $order_by;
                }
            }

            if (!empty($order_by_clause)) {
                $this->query .= ' ORDER BY ' . implode(', ', $order_by_clause);
            }
        }

        if (isset($this->limit)) {
            $this->query .= ' LIMIT ' . $this->get_placeholder_by_value($this->limit);
            $this->add_binding($this->limit);
        }

        if (isset($this->offset)) {
            $this->query .= ' OFFSET ' . $this->get_placeholder_by_value($this->offset);
            $this->add_binding($this->offset);
        }

        $this->query .= ';';

        return $this->query;
    }

    /**
     * Prepare a query and its bindings using named placeholders.
     *
     * @param string $sql
     * @param array $bindings
     * @return string
     * @throws QueryException
     */
    protected function prepare(string $sql, array $bindings = [])
    {
        $accepted_types = ['string', 'integer', 'double', 'boolean'];
        $type_map = [
            'string'  => '%s',
            'integer' => '%d',
            'double'  => '%f',
            'boolean' => '%d',
        ];

        $binding_map = [];

        foreach ($bindings as $key => $value) {
            $type = gettype($value);
            $type = in_array($type, $accepted_types, true) ? $type : 'string';
            $specifier = $type_map[$type];

            switch ($type) {
                case 'boolean':
                    $value = (int) $value;
                    break;
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'double':
                    $value = (float) $value;
                    break;
                case 'string':
                default:
                    $value = is_null($value) ? null : (string) $value;
            }

            $binding_map[$key] = [
                'specifier' => $specifier,
                'value'     => $value,
            ];
        }

        $prepared_bindings = [];

        $sql = preg_replace_callback(
            '/:(\w+)/',
            function ($matches) use ($binding_map, &$prepared_bindings, $sql) {
                $key = $matches[1];

                if (!isset($binding_map[$key])) {
                    throw new QueryException(sprintf(
                        /* translators: 1: binding key, 2: SQL query */
                        esc_html__('Missing binding for parameter \':%1$s\' in SQL: %2$s', 'growfund'),
                        esc_html($key),
                        esc_html($sql)
                    ));
                }

                $binding = $binding_map[$key];

                if ($binding['value'] === null) {
                    return 'NULL';
                }

                $prepared_bindings[] = $binding['value'];
                return $binding['specifier'];
            },
            $sql
        );

        return $this->db->prepare($sql, $prepared_bindings);
    }


    /**
     * Get the placeholder type based on value.
     *
     * @param mixed $value
     * @return string
     */
    protected function get_placeholder_by_value($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_int($value)) {
            return '%d';
        } elseif (is_float($value)) {
            return '%f';
        }

        return '%s';
    }

    /**
     * Adds a value to the bindings array, but only if it's not null.
     * This is useful for adding values to the bindings array that may or may not exist.
     *
     * @param mixed $value
     * @return void
     */
    protected function add_binding($value)
    {
        if (!is_null($value)) {
            $this->bindings[] = $value;
        }
    }

    /**
     * Check if WHERE clauses exist.
     *
     * @return bool
     */
    protected function has_where_clause()
    {
        return is_array($this->where_clauses) && count($this->where_clauses) > 0;
    }

    /**
     * Check if WHERE BETWEEN clauses exist.
     *
     * @return bool
     */
    protected function has_where_between_clause()
    {
        return is_array($this->where_between_clauses) && count($this->where_between_clauses) > 0;
    }

    /**
     * Check if OR WHERE clauses exist.
     *
     * @return bool
     */
    protected function has_or_where_clause()
    {
        return is_array($this->or_where_clauses) && count($this->or_where_clauses) > 0;
    }

    /**
     * Check for error
     *
     * @return void
     * @throws QueryException
     */
    protected function check_error()
    {
        if (!empty($this->db->last_error)) {
            /* translators: %s: database error */
            throw new QueryException(sprintf(esc_html__('Database error: %s', 'growfund'), esc_html($this->db->last_error)));
        }
    }
}
