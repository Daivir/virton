<?php
namespace Virton;

/*
 * TODO: faire un __invoke appellant un validateur du meme nom (slug, email):
 *
 */

use Virton\Database\Table;
use Virton\Validator\ValidationError;

/**
 * Handles data validations.
 *
 * Class Validator
 * @package Virton
 */
class Validator
{
    /**
     * Types of extensions allowed for uploads.
     */
    private const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf'
    ];

    /**
     * @var array
     */
    private $params;

    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * Validator constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Verifies that the fields are required.
     * @param mixed[] $keys
     * @return self
     */
    public function required(...$keys): self
    {
        if (is_array($keys[0])) {
            $keys = $keys[0];
        }
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, 'required');
            }
        }
        return $this;
    }

    /**
     * Verifies that the fields are not empty.
     * @param string[] $keys
     * @return self
     */
    public function notEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, 'empty');
            }
        }
        return $this;
    }

    /**
     * Verifies that the field is at the right length.
     * @param string $key
     * @param int|null $min
     * @param int|null $max
     * @return Validator
     */
    public function length(string $key, ?int $min, ?int $max = null): self
    {
        $value = $this->getValue($key);
        $length = mb_strlen($value);
        if ((!is_null($min) && !is_null($max)) && ($length < $min || $length > $max)) {
            $this->addError($key, 'length.between', [$min, $max]);
            return $this;
        }
        if (!is_null($min) && $length < $min) {
            $this->addError($key, 'length.min', [$min]);
            return $this;
        }
        if (!is_null($max) && $length > $max) {
            $this->addError($key, 'length.max', [$max]);
            return $this;
        }
        return $this;
    }

    /**
     * Verifies that the field is a slug.
     * @param string $key
     * @return self
     */
    public function slug(string $key): self
    {
        $value = $this->getValue($key);
        $pattern = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
        if (!is_null($value) && !preg_match($pattern, $value)) {
            $this->addError($key, 'slug');
        }
        return $this;
    }

    /**
     * Verifies that the field is numeric.
     * @param string $key
     * @return self
     */
    public function numeric(string $key): self
    {
        $value = $this->getValue($key);
        if (!is_numeric($value)) {
            $this->addError($key, 'numeric');
        }
        return $this;
    }

    /**
     * Verifies that the field is a DateTime format.
     * @param string $key
     * @param string $format
     * @return self
     */
    public function dateTime(string $key, string $format = 'Y-m-d H:i:s'): self
    {
        $value = $this->getValue($key);
        $dateTime = \DateTime::createFromFormat($format, $value);
        $errors = \DateTime::getLastErrors();
        if (($errors['error_count'] || $errors['warning_count']) > 0 || $dateTime === false) {
            $this->addError($key, 'datetime', [$format]);
        }
        return $this;
    }

    /**
     * Verifies that the field has been successfully uploaded.
     * @param string $key
     * @return self
     */
    public function uploaded(string $key): self
    {
        $file = $this->getValue($key);
        if ($file === null || $file->getError() !== UPLOAD_ERR_OK) {
            $this->addError($key, 'uploaded');
        }
        return $this;
    }

    /**
     * Verifies if the email is valid.
     * @param string $key
     * @return self
     */
    public function email(string $key)
    {
        if (filter_var($this->getValue($key), FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($key, 'email');
        }
        return $this;
    }

    /**
     * Verifies if the key passed is equals to the
     * @param string $key
     * @return self
     */
    public function confirm(string $key): self
    {
        $value = $this->getValue($key);
        $valueConfirm = $this->getValue("{$key}_confirm");
        if (($value === $valueConfirm) === false) {
            $this->addError($key, 'confirm');
        }
        return $this;
    }

    /**
     * Verifies that the field matches the entered extensions.
     * @param string $key
     * @param array $extensions
     * @return self
     */
    public function extension(string $key, array $extensions): self
    {
        /** @var \Psr\Http\Message\UploadedFileInterface $file */
        $file = $this->getValue($key);
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;
            if (!in_array($extension, $extensions) || $expectedType !== $type) {
                $this->addError($key, 'filetype', [join(', ', $extensions)]);
            }
        }
        return $this;
    }

    /**
     * Verifies that the field is already existing in the database.
     * @param string $key
     * @param string $table
     * @param \PDO $pdo
     * @return self
     */
    public function exists(string $key, string $table, \PDO $pdo): self
    {
        $value = $this->getValue($key);
        $statement = $pdo->prepare("SELECT $table.id FROM $table WHERE id = ?");
        $statement->execute([$value]);
        if ($statement->fetchColumn() === false) {
            $this->addError($key, 'exists', [$value]);
        }
        return $this;
    }

    /**
     * Verifies that the field is unique in the database.
     * @param string $key
     * @param string|Table $table
     * @param null|\PDO $pdo
     * @param int|null $ignore
     * @return self
     */
    public function unique(string $key, $table, ?\PDO $pdo = null, ?int $ignore = null): self
    {
        if ($table instanceof Table) {
            $pdo = $table->getPdo();
            $table = $table->getTable();
        }
        $value = $this->getValue($key);
        $query = "SELECT $table.id FROM $table WHERE $key = ?";
        $params = [$value];
        if (!is_null($ignore)) {
            $query .= " AND id != ?";
            $params[] = $ignore;
        }
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        if ($statement->fetchColumn() !== false) {
            $this->addError($key, 'unique', [$value]);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Retrieves errors.
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Adds an error.
     * @param string $key
     * @param string $rule
     * @param array $attributes
     * @return void
     */
    private function addError(string $key, string $rule, array $attributes = []): void
    {
        $this->errors[$key] = new ValidationError($key, $rule, $attributes);
    }

    /**
     * Gets value from key.
     * @param string $key
     * @return mixed|null
     */
    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }
}
