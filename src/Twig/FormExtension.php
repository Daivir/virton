<?php
namespace Virton\Twig;

/**
 * Implements extension about HTML inputs
 *
 * Class FormExtension
 * @package Virton\Twig
 */
class FormExtension extends \Twig_Extension
{
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('field', [$this, 'field'], [
                'is_safe' => ['html'],
                'needs_context' => true
            ])
        ];
    }

    /**
     * Generates the HTML of an inputs
     * @param array $context
     * @param string $key
     * @param mixed $value
     * @param string|null $label
     * @param array $options
     * @param array $attributes
     * @return string
     */
    public function field(
        array $context,
        string $key,
        $value,
        ?string $label = null,
        array $options = [],
        array $attributes = []
    ): string {
        $type = $options['type'] ?? 'text';
        //bootstrap
        $class = 'form-group';
        $value = $this->convertValue($value);
        $attributes = array_merge([
            'class' => trim('form-control ' . ($options['class'] ?? '')),
            'name' => $key,
            'id' => $key
        ], $attributes);
        $error = $this->getErrorHtml($context, $key);
        if ($error) {
            $attributes['class'] .= ' is-invalid';
        }
        switch ((string)$type) {
            case 'textarea':
                $input = $this->textarea($value, $attributes);
                break;
            case 'number':
                $input = $this->input($value, $attributes, $type);
                break;
            case 'checkbox':
                $class .= ' form-check';
                $input = $this->checkbox($value, $attributes);
                break;
            case array_key_exists('options', $options):
                $input = $this->select($value, $options['options'], $attributes);
                break;
            default:
                $input = $this->input($value, $attributes, $type);
                break;
        }
        return $this->buildOutputHtml($type, $input, $key, $label, $error, $class);
    }

    /**
     * @param string $type
     * @param string $input
     * @param string $key
     * @param null|string $label
     * @param bool|string $error
     * @param string $class
     * @return string
     */
    private function buildOutputHtml(
        string $type,
        string $input,
        string $key,
        ?string $label,
        $error,
        string $class
    ): string {
        $html = [];
        $html[] = "<div class=\"$class\">";
	    $html[] = "<label for=\"$key\">";
        if ($type === 'checkbox') {
            $html[] = $input;
            $html[] = "<span>$label</span>";
            $html[] = "</label>";
        } else {
            $html[] = "$label</label>";
            $html[] = $input;
        }
        $html[] = $error;
        $html[] = "</div>";
        return join('', $html);
    }

    /**
     * Text|Default input
     * @param string|null $value
     * @param array $attributes
     * @param string $type
     * @return string
     */
    private function input(?string $value, array $attributes, string $type): string
    {
        return "<input type=\"$type\" " . $this->getHtmlFromArray($attributes) . " value=\"$value\" />";
    }

    /**
     * Checkbox input
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function checkbox(?string $value, array $attributes): string
    {
        $html = "<input type=\"hidden\" name=\"{$attributes['name']}\" value=\"0\">";
        if ($value) {
            $attributes['checked'] = true;
        }
        return $html . "<input type=\"checkbox\" " . $this->getHtmlFromArray($attributes) . " value=\"1\" />";
    }

    /**
     * Textarea input
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function textarea(?string $value, array $attributes): string
    {
        return "<textarea " . $this->getHtmlFromArray($attributes) . " rows=\"12\">$value</textarea>";
    }

    /**
     * Select input
     * @param string|null $value
     * @param array $options
     * @param array|\Framework\Database\Query $attributes
     * @return string
     */
    private function select(?string $value, $options, array $attributes): string
    {
        $htmlOptions = array_reduce(array_keys($options), function (string $html, string $key) use ($options, $value) {
            $params = ['value' => $key, 'selected' => $key === $value];
            return "$html<option {$this->getHtmlFromArray($params)}>$options[$key]</option>";
        }, "");
        return "<select " . $this->getHtmlFromArray($attributes) . ">$htmlOptions</select>";
    }

    /**
     * Gets HTML errors
     * @param mixed $context
     * @param mixed $key
     * @return string
     */
    private function getErrorHtml($context, $key): string
    {
        $error = $context['errors'][$key] ?? false;
        if ($error) {
            return "<div class=\"invalid-feedback\">$error</div>";
        }
        return false;
    }

    /**
     * Converts arrays into HTML tag attributes
     * @param array $attributes
     * @return string
     */
    private function getHtmlFromArray(array $attributes)
    {
        $htmlParts = [];

        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $htmlParts[] = "$key";
            } elseif ($value !== false) {
                $htmlParts[] = "$key=\"$value\"";
            }
        }

        return implode(' ', $htmlParts);
    }

    /**
     * Converts DateTime to string
     * @param mixed $value
     * @return string
     */
    private function convertValue($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return (string)$value;
    }
}
