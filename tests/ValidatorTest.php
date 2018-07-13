<?php
namespace Tests;

use Virton\Validator;
use GuzzleHttp\Psr7\UploadedFile;
use Tests\DatabaseTestCase;

class ValidatorTest extends DatabaseTestCase
{
    private function buildValidator(array $params)
    {
        return (new Validator($params));
    }

    public function testNotEmpty()
    {
        $params = ['name' => 'Joe', 'content' => ''];
        $errors = $this->buildValidator($params)
            ->notEmpty('name', 'content')
            ->getErrors();
        $this->assertCount(1, $errors);
    }

    public function testRequiredError()
    {
        $params = ['name' => 'Joe'];
        $errors = $this->buildValidator($params)
            ->required('name', 'content')
            ->getErrors();
        $this->assertCount(1, $errors);
    }

    public function testRequiredSuccess()
    {
        $params = ['name' => 'Joe', 'content' => 'Lorem ipsum'];
        $errors = $this->buildValidator($params)
            ->required('name', 'content')
            ->getErrors();
        $this->assertCount(0, $errors);
    }

    public function testLength()
    {
        $params = ['name' => '123456789'];

        $this->assertCount(0, $this->buildValidator($params)->length('name', 3)->getErrors());
        $this->assertCount(1, $this->buildValidator($params)->length('name', 10)->getErrors());
        $this->assertCount(1, $this->buildValidator($params)->length('name', 3, 4)->getErrors());
        $this->assertCount(0, $this->buildValidator($params)->length('name', 3, 20)->getErrors());
        $this->assertCount(0, $this->buildValidator($params)->length('name', null, 20)->getErrors());
        $this->assertCount(1, $this->buildValidator($params)->length('name', null, 6)->getErrors());
    }

    public function testDateTime()
    {
        $this->assertCount(0, $this->buildValidator(['date' => '2001-02-03 01:02:03'])
        ->dateTime('date')->getErrors());
        $this->assertCount(1, $this->buildValidator(['date' => '2001-02-03'])
        ->dateTime('date')->getErrors());
        $this->assertCount(1, $this->buildValidator(['date' => '2001-21-03'])
        ->dateTime('date')->getErrors());
        $this->assertCount(1, $this->buildValidator(['date' => '2013-02-29 11:12:13'])
        ->dateTime('date')->getErrors());
    }

    public function testSlugError()
    {
        $params = [
            'slug' => 'Slug-test-1',
            'slug2' => 'Slug_test-2',
            'slug3' => 'Slug--test_3',
            'slug4' => 'slug-test-'
        ];
        $errors = $this->buildValidator($params)
            ->slug('slug')
            ->slug('slug2')
            ->slug('slug3')
            ->slug('slug4')
            ->slug('slug5')
            ->getErrors();
        $this->assertCount(4, $errors);
    }

    public function testSlugSuccess()
    {
        $params = ['slug' => 'slug-test-1', 'slug2' => 'slug'];
        $errors = $this->buildValidator($params)
            ->slug('slug')
            ->slug('slug2')
            ->getErrors();
        $this->assertCount(0, $errors);
    }

    public function testExists()
    {
        $pdo = $this->getPdo();
        $pdo->exec("CREATE TABLE test (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255))");
        $pdo->exec('INSERT INTO test (name) VALUES ("a1")');
        $errors = $this->buildValidator(['category' => 1])
            ->exists('category', 'test', $pdo)
            ->isValid();
        $this->assertTrue($errors);
        $errorsFailure = $this->buildValidator(['category' => 1212])
            ->exists('category', 'test', $pdo)
            ->isValid();
        $this->assertFalse($errorsFailure);
    }

    public function testUnique()
    {
        $pdo = $this->getPdo();
        $pdo->exec("CREATE TABLE test (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(255))");
        $pdo->exec('INSERT INTO test (name) VALUES ("a1")');
        $pdo->exec('INSERT INTO test (name) VALUES ("a2")');
        $this->assertTrue($this->buildValidator(['name' => 'a1'])->unique('name', 'test', $pdo, 1)->isValid());
        $this->assertTrue($this->buildValidator(['name' => 'a111'])->unique('name', 'test', $pdo)->isValid());
        $this->assertFalse($this->buildValidator(['name' => 'a1'])->unique('name', 'test', $pdo)->isValid());
        $this->assertFalse($this->buildValidator(['name' => 'a2'])->unique('name', 'test', $pdo, 1)->isValid());
    }

    public function testUploadedFile()
    {
        $file = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError'])
            ->getMock();
        $file->expects($this->once())->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file2 = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError'])
            ->getMock();
        $file2->expects($this->once())->method('getError')->willReturn(UPLOAD_ERR_CANT_WRITE);
        $this->assertTrue($this->buildValidator(['image' => $file])->uploaded('image')->isValid());
        $this->assertFalse($this->buildValidator(['image' => $file2])->uploaded('image')->isValid());
    }

    public function testExtension()
    {
        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->expects($this->any())->method('getClientFileName')->willReturn('demo.jpg');
        $file->expects($this->any())
            ->method('getClientMediaType')
            ->will($this->onConsecutiveCalls('image/jpeg', 'fake/php'));
        $this->assertTrue($this->buildValidator(['image' => $file])->extension('image', ['jpg'])->isValid());
        $this->assertFalse($this->buildValidator(['image' => $file])->extension('image', ['jpg'])->isValid());
    }

    public function testEmail()
    {
        $this->assertTrue(
            $this->buildValidator(['email' => 'test@local.dev'])
                ->email('email')
                ->isValid()
        );
        $this->assertFalse(
            $this->buildValidator(['email' => 'invalid-email'])
                ->email('email')
                ->isValid()
        );
    }

    public function testConfirm()
    {
        $this->assertFalse($this->buildValidator(['slug' => 'test'])->confirm('slug')->isValid());
        $this->assertFalse($this->buildValidator(['slug' => 'test', 'slug_confirm' => 'test-invalid'])->confirm('slug')
            ->isValid());
        $this->assertTrue($this->buildValidator(['slug' => 'test', 'slug_confirm' => 'test'])->confirm('slug')
            ->isValid());
    }
}
