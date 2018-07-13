<?php
namespace Virton\Twig;

/**
 * Implements an extension about datetime formatting
 *
 * Class TimeExtension
 * @package Virton\Twig
 */
class TimeExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('ago', [$this, 'ago'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Relative date/time input
     * @param \DateTime $date
     * @param string $format
     * @return string
     */
    public function ago(\DateTime $date, string $format = 'd/m/Y H:i')
    {
        $dateFormatDisplay = $date->format($format);
        $dateFormatParam = $date->format(\DateTime::ISO8601);
        return "<span class=\"timeago\" datetime=\"$dateFormatParam\">$dateFormatDisplay</span>";
    }
}
