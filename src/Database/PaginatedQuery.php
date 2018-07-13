<?php
namespace Virton\Database;

use Virton\Database\QueryResult;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter
 *
 * Class PaginatedQuery
 * @package Virton\Database
 */
class PaginatedQuery implements AdapterInterface
{
    private $query;

    /**
     * PaginatedQuery constructor
     *
     * @param Query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @return int
     */
    public function getNbResults(): int
    {
        return $this->query->count();
    }
    
    /**
     * @param int $offset
     * @param int $length
     * @return QueryResult
     */
    public function getSlice($offset, $length): QueryResult
    {
        $query = clone $this->query;
        return $query->limit($length, $offset)->fetchAll();
    }
}
