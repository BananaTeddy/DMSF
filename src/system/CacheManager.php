<?php declare(strict_types=1);
namespace system;

/**
 * VERY basic 'cache manager'. Use it to clear the cache with a call to the 'clear'-function
 */
class CacheManager {
    
    const TEMPLATE = 'Templates';
    const JAVASCRIPT = 'JavaScript';

    /**
     * Clears the specified cache
     *
     * @param string $CACHE_TYPE
     * 
     * @return void
     **/
    public static function clear(string $CACHE_TYPE): void
    {
        $cwd = getcwd();
        chmod("{$cwd}\\cache", 0777);
        $cachedFiles = array_diff( scandir("{$cwd}/cache/{$CACHE_TYPE}"), ['.', '..']);
        foreach ($cachedFiles as $cachedFile) {
            self::deleteRecursive("{$cwd}/cache/{$CACHE_TYPE}/{$cachedFile}");
        }
    }

    /**
     * Deletes files recursively
     *
     * @param string $file
     * 
     * @return void
     **/
    protected static function deleteRecursive(string $file): void
    {
        if (is_dir($file)) {
            $subDirs = array_diff( scandir($file), ['.', '..']);
            foreach ($subDirs as $subDir) {
                self::deleteRecursive("{$file}/{$subDir}");
            }
        } else {
            if (is_file($file))
                unlink($file);
        }
    }
}
