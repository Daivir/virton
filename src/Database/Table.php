<?php
namespace Virton\Database;

use App\Blog\Entity\Post;
use Virton\Database\Query;
use Virton\Database\PaginatedQuery;
use Pagerfanta\Pagerfanta;

/**
 * Class Table
 * @package Virton\Database
 */
class Table
{
    /**
     * @var null|\PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var \PDO
     */
    protected $entity = \stdClass::class;

    /**
     * Table constructor
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Build the query
     *
     * @return Query
     */
    public function makeQuery(): Query
    {
        return (new Query($this->pdo))
            ->from($this->table, $this->table[0])
            ->into($this->entity)
        ;
    }

    /**
     * Retreive all records
     *
     * @return Query
     */
    public function findAll(): Query
    {
        return $this->makeQuery();
    }

    /**
     * Retrieves a record from its id
     *
     * @param int $id
     * @return mixed
     * @throws NoRecordException
     */
    public function find(int $id)
    {
        return $this->makeQuery()->where("id = $id")->fetchOrException();
    }

    /**
     * Retreives records from a field.
     * @param string $field
     * @param string $value
     * @return object
     */
    public function findBy(string $field, string $value)
    {
        return $this->makeQuery()->where("$field = :field")->params(['field' => $value])->fetchOrException();
    }

    /**
     * Retreive a key/value record list
     *
     * @return array
     */
    public function findList(): array
    {
        $results = $this->pdo
            ->query("SELECT id, name FROM $this->table")
            ->fetchAll(\PDO::FETCH_NUM);
        $list = [];
        foreach ($results as $result) {
            $list[$result[0]] = $result[1];
        }
        return $list;
    }

    /**
     * Retreives the number of records
     *
     * @return int
     */
    public function count(): int
    {
        return $this->makeQuery()->count();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function exists(string $id): bool
    {
        $statement = $this->makeQuery()->where('id = ?')->params([$id])->execute();
        return $statement->fetchColumn() !== false;
    }

    /**
     * Update records in the database
     *
     * @param int $id
     * @param array $fields
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldQuery = $this->buildFieldQuery($params);
        $params['id'] = $id;
        $statement = $this->pdo->prepare("UPDATE $this->table SET $fieldQuery WHERE id = :id");
        return $statement->execute($params);
    }

    /**
     * Insert datas in the database
     *
     * @param array $params Ex: ['id' => "1", 'slug' => "a-slug"]
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = array_keys($params);
        $values = join(', ', array_map(function ($field) {
            return ":$field";
        }, $fields));
        $fields = join(', ', array_keys($params));
        $statement = $this->pdo->prepare("INSERT INTO $this->table ($fields) VALUES ($values)");
        return $statement->execute($params);
    }

    /**
     * Delete datas in the database
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM $this->table WHERE id = ?");
        return $statement->execute([$id]);
    }

    /**
     * @param array $params
     * @return string
     */
    private function buildFieldQuery(array $params): string
    {
        return join(', ', array_map(function ($field) {
            return "$field = :$field";
        }, array_keys($params)));
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}
