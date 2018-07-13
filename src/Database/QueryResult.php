<?php
namespace Virton\Database;

/**
 * Class QueryResult
 * @package Virton\Database
 */
class QueryResult implements
    \ArrayAccess,
    \Iterator,
	\JsonSerializable
{
    /**
     * @var array
     */
    private $records;

    /**
     * Set the default index to 0
     * @var integer
     */
    private $index = 0;
    
    /**
     * @var array
     */
    private $hydratedRecords = [];

    /**
     * @var string|null
     */
    private $entity;

    /**
     * QueryResult constructor
     *
     * @param array $records
     * @param string|null $entity
     */
    public function __construct(array $records, ?string $entity = null)
    {
        $this->records = $records;
        $this->entity = $entity;
    }

    /**
     * @param int $index
     * @return object|string
     */
    public function get(int $index)
    {
        if ($this->entity) {
            if (!isset($this->hydratedRecords[$index])) {
                $this->hydratedRecords[$index] = Hydrator::hydrate($this->records[$index], $this->entity);
            }
            return $this->hydratedRecords[$index];
        }
        return $this->entity;
    }

    /**
     * Return an array with hydrated records.
     * @return array
     */
    public function toArray(): array
    {
        $records = [];
        foreach ($this->records as $k => $v) {
            $records[] = $this->get($k);
        }
        return $records;
    }

    /**
     * @return object|string
     */
    public function current()
    {
        return $this->get($this->index);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return boolean
     */
    public function valid(): bool
    {
        return isset($this->records[$this->index]);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @param int $offset
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->records[$offset]);
    }

    /**
     * @param int $offset
     * @return object|string
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param int $offset
     * @param mixed $value
     * @return void
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception("Can't alter records");
    }

	/**
	 * @param int $offset
	 * @return void
	 * @throws \Exception
	 */
    public function offsetUnset($offset)
    {
        throw new \Exception("Can't alter records");
    }

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}
}
