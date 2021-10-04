<?php declare(strict_types=1);
namespace system\Database;

class QueryFilter {
    
    public const Equal          = 'Equal';
    public const NotEqual       = 'NotEqual';
    public const Less           = 'Less';
    public const LessEqual      = 'LessEqual';
    public const Greater        = 'Greater';
    public const GreaterEqual   = 'GreaterEqual';
    public const Like           = 'Like';
    public const StartsWith     = 'StartsWith';
    public const EndsWith       = 'EndsWith';
    public const HasFlag        = 'HasFlag';
    public const HasFlagNot     = 'HasFlagNot';

    /**
     * First filter in list
     * 
     * @param string $filter
     * @param string $property
     * @param mixed $value
     * 
     * @return string
     */
    public static function Filter(string $filter, string $property, $value)
    {
        $condition = self::getFilter($filter, $property, $value);
        return "{$condition}";
    }

    /**
     * AND conjunction
     * 
     * @param string $filter
     * @param string $property
     * @param mixed $value
     * 
     * @return string
     */
    public static function AndFilter(string $filter, string $property, $value)
    {
        $condition = self::getFilter($filter, $property, $value);
        return "AND {$condition}";
    }

    /**
     * OR conjunction
     * 
     * @param string $filter
     * @param string $property
     * @param mixed $value
     * 
     * @return string
     */
    public static function OrFilter(string $filter, string $property, $value)
    {
        $condition = self::getFilter($filter, $property, $value);
        return "OR {$condition}";
    }

    /**
     * Groups given filters
     * 
     * @param array $filters array of filters
     * 
     * @return string
     */
    public static function Group(array $filters)
    {
        $conditions = "";
        foreach ($filters as $key => $value) {
            $conditions .= " {$value} ";
        }
        $conditions = "({$conditions})";
        return $conditions;
    }

    /**
     * Adds another group of filters with an OR statement
     * 
     * @param array $filters array of filters
     * 
     * @return string
     */
    public static function OrGroup(array $filters)
    {
        $conditions = "";
        foreach ($filters as $key => $value) {
            $conditions .= " {$value} ";
        }
        $conditions = "OR ({$conditions})";
        return $conditions;
    }

    /**
     * Adds another group of filters with an AND statement
     * 
     * @param array $filters array of filters
     * 
     * @return string
     */
    public static function AndGroup(array $filters)
    {
        $conditions = "";
        foreach ($filters as $key => $value) {
            $conditions .= " {$value} ";
        }
        $conditions = "AND ({$conditions})";
        return $conditions;
    }

    /**
     * get the filter dynamically
     * 
     * @param string $filter the filter function to use
     * @param string $property the property to use the filter on
     * @param mixed $value the value for the filter
     * 
     * @return string
     */
    private static function getFilter(string $filter, string $property, $value)
    {
        $function = "filter{$filter}";
        $condition = self::$function($property, $value);
        return $condition;
    }

    private static function filterEqual(string $property, $value)
    {
        if (is_null($value)) {
            $condition = "{$property} IS NULL";
        } else if (is_string($value)) {
            $condition = "{$property} = '{$value}'";
        } else {
            $condition = "{$property} = {$value}";
        }
        return $condition;
    }

    private static function filterNotEqual(string $property, $value)
    {
        if (is_null($value)) {
            $condition = "{$property} IS NOT NULL";
        } else if (is_string($value)) {
            $condition = "{$property} != '{$value}'";
        } else {
            $condition = "{$property} != {$value}";
        }
        return $condition;
    }

    private static function filterLess(string $property, $value)
    {
        if (is_string($value)) {
            $condition = "{$property} < '{$value}'";
        } else {
            $condition = "{$property} < {$value}";
        }
        return $condition;
    }

    private static function filterLessEqual(string $property, $value)
    {
        if (is_string($value)) {
            $condition = "{$property} <= '{$value}'";
        } else {
            $condition = "{$property} <= {$value}";
        }
        return $condition;
    }

    private static function filterGreater(string $property, $value)
    {
        if (is_string($value)) {
            $condition = "{$property} > '{$value}'";
        } else {
            $condition = "{$property} > {$value}";
        }
        return $condition;
    }

    private static function filterGreaterEqual(string $property, $value)
    {
        if (is_string($value)) {
            $condition = "{$property} >= '{$value}'";
        } else {
            $condition = "{$property} >= {$value}";
        }
        return $condition;
    }

    private static function filterLike(string $property, string $value)
    {
        return "{$property} LIKE '%{$value}%'";
    }

    private static function filterStartsWith(string $property, string $value)
    {
        return "{$property} LIKE '{$value}%'";
    }

    private static function filterEndsWith(string $property, string $value)
    {
        return "{$property} LIKE '%{$value}'";
    }

    private static function filterHasFlag(string $property, int $value)
    {
        return "({$property} & {$value}) != 0";
    }

    private static function filterHasFlagNot(string $property, int $value)
    {
        return "({$property} & {$value}) = 0";
    }
}
