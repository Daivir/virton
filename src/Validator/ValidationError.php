<?php
namespace Virton\Validator;

/**
 * Handles error messages from form validation.
 *
 * Class ValidationError
 * @package Virton\Validator
 */
class ValidationError
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $rule;

    /**
     * @var array
     */
    private $attributes;

    /**
     * Error messages.
     * @var array
     */
    private $messages = [
        'confirm' => 'Please confirm the field "%s"',
        'datetime' => 'The field "%s" must be a valid format (%s)',
        'email' => 'The field "%s" does not appear to be valid',
        'empty' => 'The field "%s" can not be empty',
        'exists' => 'The field "%s" does not exists',
        'filetype' => 'The field "%s" isn\'t a valid format (%s)',
        'length.between' => 'The field "%s" must contain between %d and %d characters long',
        'length.max' => 'The field "%s" must contain at most %d characters long',
        'length.min' => 'The field "%s" must contain at least %d characters long',
        'numeric' => 'The field "%s" must be numeric',
        'required' => 'The field "%s" is required',
        'slug' => 'The field "%s" is not a valid slug',
        'unique' => 'The field "%s" must be unique',
        'uploaded' => 'You must upload a file'
    ];

    /**
     * ValidationError constructor.
     * @param string $key
     * @param string $rule
     * @param array $attributes
     */
    public function __construct(string $key, string $rule, array $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!array_key_exists($this->rule, $this->messages)) {
            return "The \"{$this->key}\" field does not correspond to the rule {$this->rule}";
        }
        $params = array_merge([$this->messages[$this->rule], $this->key], $this->attributes);
        return (string)call_user_func_array('sprintf', $params);
    }
}
