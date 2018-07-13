<?php
namespace Tests\Database;

use Virton\Database\Pager;
use Tests\DatabaseTestCase;
use Virton\Database\PaginatedQuery;

use Virton\Database\Query;

class QueryTest extends DatabaseTestCase
{
    public function testSimpleQuery()
    {
        $query = (new Query)
            ->from('posts')
            ->select('name');
        $this->assertEquals("SELECT name FROM posts", (string)$query);
    }

    public function testWithWhere()
    {
        $query = (new Query)
            ->from('posts', 'p')
            ->where('a = :a')
        ;
        $this->assertEquals("SELECT * FROM posts AS p WHERE (a = :a)", (string)$query);

        $query2 = (new Query)
            ->from('posts', 'p')
            ->where('a = :a OR b = :b', 'c = :c')
        ;
        $this->assertEquals("SELECT * FROM posts AS p WHERE (a = :a OR b = :b) AND (c = :c)", (string)$query2);

        $query3 = (new Query)
            ->from('posts', 'p')
            ->where('a = :a OR b = :b')
            ->where('c = :c')
        ;
        $this->assertEquals("SELECT * FROM posts AS p WHERE (a = :a OR b = :b) AND (c = :c)", (string)$query3);
    }

    public function testJoinQuery()
    {
        $query = (new Query)
            ->select('p.name')
            ->from('posts', 'p')
            ->join('categories AS c', 'c.id = p.category_id')
            ->join('categories AS c2', 'c2.id = p.category_id', 'inner')
        ;
        $select = "SELECT p.name FROM posts AS p LEFT JOIN categories AS c ON c.id = p.category_id";
	    $queryTest = "$select INNER JOIN categories AS c2 ON c2.id = p.category_id";
        $this->assertSimilarString(
            $queryTest,
            (string)$query
        );
    }

    public function testWithWhereParams()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);

        $queryBuilder = (new Query($pdo))
            ->from('posts')
            ->where("id = ? OR id = ?")
            ->params([1, 2])
            ->execute()
            ->fetchAll()
        ;

        $this->assertEquals($pdo->query("SELECT * FROM posts WHERE id = 1 OR id = 2")->fetchAll(), $queryBuilder);
    }

    public function testFetchAll()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);
        $query = (new Query($pdo))
            ->from('posts', 'p')
            ->count()
        ;
        $this->assertEquals(100, $query);
        $query2 = (new Query($pdo))
            ->from('posts', 'p')
            ->where('p.id < :number')
            ->params(['number' => 50])
            ->count()
        ;
        $this->assertEquals(50 - 1, $query2);
    }

    public function testHydrateEntity()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);
        $query = (new Query($pdo))
            ->from('posts')
            ->into(Test::class)
            ->fetchAll()
        ;
        $this->assertEquals('test', substr($query[0]->getSlug(), -4));
    }

    public function testLazyHydrate()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);
        $posts = (new Query($pdo))
            ->from('posts')
            ->into(Test::class)
            ->fetchAll();

        $post = $posts[0];
        $post2 = $posts[0];

        $this->assertSame($post, $post2);
    }

    public function testFetchAllPaginated()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);
        $query = (new Query($pdo))
            ->from('posts')
            ->paginate(10, 1)
        ;

        $paginatedStatement = new PaginatedQuery((new Query($pdo))->from('posts'));
        $paginatedQuery = (new Pager($paginatedStatement))
            ->setMaxPerPage(10)
            ->setCurrentPage(1)
        ;
        $this->assertEquals($paginatedQuery, $query);
    }

    public function testGroupQuery()
    {
        $query = (new Query)
            ->from('posts')
            ->group('id')
        ;

        $query2 = (new Query)
            ->from('posts')
            ->group('id', 'slug')
        ;
        $this->assertEquals("SELECT * FROM posts GROUP BY id", (string)$query);
        $this->assertEquals("SELECT * FROM posts GROUP BY id, slug", (string)$query2);
    }

    public function testLimitOrderQuery()
    {
        $query = (new Query)
            ->from('posts', 'p')
            ->select('name')
            ->order('id DESC')
            ->order('name ASC')
            ->limit(10, 5)
        ;

        $this->assertEquals(
            "SELECT name FROM posts AS p ORDER BY id DESC, name ASC LIMIT 5, 10",
            (string)$query
        );
    }

    private function assertSimilarString(string $expected, string $actual)
    {
        $this->assertEquals($this->strtrim($expected), $this->strtrim($actual));
    }

    private function strtrim(string $string)
    {
        $lines = explode(PHP_EOL, $string);
        $lines = array_map('trim', $lines);
        return implode('', $lines);
    }
}
