<?php

declare (strict_types = 1);

namespace application\Helper;

class FileUpload {
    /**
     * Returns an reordered file array from $_FILES with given name
     * 
     * @param string $index
     */
    public static function multiple_files(string $index): array
    {
        $arr = [];
        foreach ($_FILES[$index] as $field => $values) {
            foreach ($values as $inx => $value) {
                $arr[$inx][$field] = $value;
            }
        }

        return $arr;
    }
}