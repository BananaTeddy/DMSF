<?php

declare(strict_types=1);

namespace system\Helper;

/**
 * Helper class for file handling
 */
class File {
    const WRITE = 'w+';
    const READ = 'r+';
    const APPEND = 'a+';

    /**
     * Checks if a file exists
     * 
     * @param string $file the filepath to check
     * 
     * @return bool
     */
    public static function Exists(string $file): bool
    {
        $cwd = getcwd();
        return file_exists("{$cwd}/{$file}");
    }

    /**
     * Tries to open a file in specified mode and returns a file handle if successful
     * 
     * @param string $file filepath to the file
     * @param string $mode the mode the file should be opened in
     */
    public static function Open(string $file, string $mode)
    {
        $cwd = getcwd();
        if ($handle = fopen("{$cwd}/{$file}", $mode)) {
            return $handle;
        } else {
            return null;
        }
    }
    
    /**
     * Closes the given filehandle
     * 
     * @param resource $handle
     * 
     * @return void
     */
    public static function Close($handle): void
    {
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    /**
     * Returns the size of the file in bytes or false on failure
     * 
     * @param string $file the filepath to the file
     * 
     * @return int|false
     */
    public static function Size(string $file)
    {
        $cwd = getcwd();
        return filesize("{$cwd}/{$file}");
    }

    /**
     * Reads all line of a file
     * 
     * @param string $file filepath to the file
     * @param bool $isAbsolute whether the filepath is absolute. DEFAULT: false
     * 
     * @return array|false
     */
    public static function ReadAllLines(string $file, bool $isAbsolute = false)
    {
        $cwd = '';
        if (! $isAbsolute) {
            $cwd = getcwd() . '/';
        }
        return file("{$cwd}{$file}", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    }
}