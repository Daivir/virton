<?php
namespace Virton\Database;

use Exception;
use PDO;
use Pagerfanta\Pagerfanta;
use Virton\Database\QueryResult;
use IteratorAggregate;

/**
 * Allow to use fluent pattern to generate SQL queries
 *
 * Class Query
 * @package Virton\Database
 */
class Query implements IteratorAggregate
{
    /**
     * @var array|null
     */
    protected $select;

    /**
     * @var string[]
     */
    protected $from;

    /**
     * @var string[]
     */
    protected $where = [];

    /**
     * @var string[]
     */
    protected $group = [];

    /**
     * @var string[]
     */
    protected $order = [];

    /**
     * @var int[]
     */
    protected $limit;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var PDO|null
     */
    protected $pdo;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @var array
     */
    protected $joins;

    /**
     * Query constructor
     *
     * @param PDO|null $pdo
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * FROM clause
     *
     * Examples:
     *  $query->from('table')       // "SELECT * FROM table"
     *  $query->from('table', 't')  // "SELECT * FROM table AS t"
     *
     * @param string $table
     * @param string|null $alias
     * @return self
     */
    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }
        return $this;
    }

    /**
     * Defines the retreival selected table
     *
     * Examples:
     *  $query->select('*')->from('table')          // "SELECT * FROM table"
     *  $query->select('id')->from('table')         // "SELECT id FROM table"
     *  $query->select('id', 'name')->from('table') // "SELECT id, name FROM table"
     *
     * @param string[] ...$fields
     * @return self
     */
    public function select(string ...$fields): self
    {
        $this->select = $fields;
        return $this;
    }

    /**
     * Defines the retreival condition
     *
     * Examples:
     *  $query->from('table', 't')->where('a = :a')
     *      // "SELECT * FROM posts AS p WHERE (a = :a)"
     *  $query->from('posts', 'p')->where('a = :a OR b = :b', 'c = :c')
     *      OR
     *  $query->from('posts', 'p')->where('a = :a OR b = :b')->where('c = :c')
     *      // "SELECT * FROM posts AS p WHERE (a = :a OR b = :b) AND (c = :c)"
     *
     * @param string[] ...$condition
     * @return self
     */
    public function where(string ...$condition): self
    {
        $this->where = array_merge($this->where, $condition);
        return $this;
    }

    /**
     * Define paramaters for execute()
     *
     * Examples:
     *  $query->from('table')->where("id = ?")->params([$id])->execute()...
     *
     * @param array $params
     * @return self
     */
    public function params(array $params): self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Define entity
     *
     * @param string $entity
     * @return self
     */
    public function into(string $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Execute the statement or the prepared statement
     *
     * @return \PDOStatement
     */
    public function execute()
    {
        $query = $this->__toString();
        if (!empty($this->params)) {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->params);
            return $statement;
        }
        return $this->pdo->query($query);
    }

    /**
     * Return the number of the column
     *
     * @return int
     */
    public function count(): int
    {
        $query = clone $this;
        $table = current($this->from);
        return $query->select("(COUNT($table.id))")->execute()->fetchColumn();
    }

    /**
     * Specifies the retreival limit
     *
     * Examples:
     *  $query->from('posts')->group('id')          // "SELECT * FROM posts GROUP BY id"
     *  $query->from('posts')->group('id', 'slug')  // "SELECT * FROM posts GROUP BY id, slug"
     *
     * @param string[] ...$column
     * @return self
     */
    public function group(string ...$column): self
    {
        $this->group = array_merge($this->group, $column);
        return $this;
    }

    /**
     * Specifies the retreival limit
     *
     * Examples:
     *  $query->from('posts')->limit(5)     // "SELECT * FROM posts LIMIT 5"
     *  $query->from('posts')->limit(3, 10) // "SELECT * FROM posts LIMIT 3, 10"
     *
     * @param int $length
     * @param int|null $offset
     * @return self
     */
    public function limit(int $length, ?int $offset = 0): self
    {
        $this->limit = "$offset, $length";
        return $this;
    }

    /**
     * Specifies the retrieval order
     *
     * Examples:
     *  $query->from('posts')->order('id ASC')
     *      // "SELECT * FROM posts ORDER BY id ASC"
     *
     * @param string $order
     * @return self
     */
    public function order(string $order): self
    {
        $this->order[] = $order;
        return $this;
    }

    /**
     * Link to a foreign column on another table.
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return self
     */
    public function join(string $table, string $condition, string $type = 'left'): self
    {
        $this->joins[$type][] = [$table, $condition];
        return $this;
    }

    /**
     * Paginate results.
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     */
    public function paginate(int $perPage, int $currentPage = 1): Pagerfanta
    {
        $statement = new PaginatedQuery($this);
        return (new Pager($statement))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * Retrieves a result.
     * @return mixed / QueryResult OR Entity
     */
    public function fetch()
    {
        $record = $this->execute()->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            return null;
        }
        if ($this->entity) {
            return Hydrator::hydrate($record, $this->entity);
        }
    }

    /**
     * Retrieves a result or throw an exception.
     * @return mixed
     * @throws NoRecordException
     */
    public function fetchOrException()
    {
        $record = $this->fetch();
        if ($record === null) {
            throw new NoRecordException;
        }
        return $record;
    }

    /**
     * Fetch all the records.
     * @return QueryResult
     */
    public function fetchAll()
    {
        return new QueryResult(
            $this->execute()->fetchAll(PDO::FETCH_ASSOC),
            $this->entity
        );
    }

    /**
     * @param int $columnNumber
     * @return mixed
     */
    public function fetchColumn(int $columnNumber = 0)
    {
        return $this->execute()->fetchColumn($columnNumber);
    }

    /**
     * Build and return the query
     *
     * @return string
     */
    public function __toString()
    {
        // SELECT
        $parts = ["SELECT"];
        if ($this->select) {
            $parts[] = join(", ", $this->select);
        } else {
            $parts[] = "*";
        }

        // FROM
        $parts[] = "FROM";
        $parts[] = $this->buildFrom();


        if (!empty($this->joins)) {
            foreach ($this->joins as $type => $joins) {
                foreach ($joins as [$table, $condition]) {
                    $parts[] = strtoupper($type) . " JOIN $table ON $condition";
                }
            }
        }

        // WHERE
        if (!empty($this->where)) {
            $parts[] = "WHERE";
            $parts[] = "(" . join(") AND (", $this->where) . ")";
        }

        // GROUP BY
        if (!empty($this->group)) {
            $parts[] = "GROUP BY";
            $parts[] = join(", ", $this->group);
        }

        // ORDER BY
        if (!empty($this->order)) {
            $parts[] = "ORDER BY";
            $parts[] = join(", ", $this->order);
        }

        // LIMIT
        if (!empty($this->limit)) {
            $parts[] = "LIMIT {$this->limit}";
        }

        return join(" ", $parts);
    }

    /**
     * Build the FROM clause
     *
     * @return string
     */
    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$key AS $value";
            } else {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }

    /**
     * @inheritDoc
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return $this->fetchAll();
    }
}
