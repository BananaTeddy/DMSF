<?php declare(strict_types=1);
namespace system\Helper;

/**
 * Base class for logging
 */
class Logger {
    
    public const INFO       = 'INFO';
    public const ERROR      = 'ERROR';
    public const WARNING    = 'WARN';
    public const DEBUG      = 'DEBUG';

    /** @var string $logFile */
    private $logFile;

    /** @var resource $logHandle */
    private $logHandle;

    public function __construct(string $filename = 'error.log') {
        $cwd = getcwd();
        $year = date('Y');
        chmod("{$cwd}/logs", 0777);
        $this->logFile = "{$cwd}/logs/{$year}/{$filename}";
        if (! is_dir("{$cwd}/logs/{$year}")) {
            mkdir("{$cwd}/logs/{$year}", 0777, true);
        }
        $this->logHandle;
    }

    /**
     * Starts logging
     * Opens the log file in append mode
     * @return void
     **/
    public function start(): void
    {
        $this->logHandle = fopen($this->logFile, 'a+');
    }

    /**
     * Logs a message to the log file
     *
     * @param string $logmessage
     * @return void
     **/
    public function log(string $logmessage, string $level = self::INFO): void
    {
        $time = $this->getLogTime();
        fwrite(
            $this->logHandle,
            "[{$time}] {$level} {$logmessage}\n"
        );
    }

    /**
     * Logs an exception
     *
     * @param \Exception $e
     * @return void
     **/
    public function logException(\Exception $e): void
    {
        $level = self::ERROR;
        $time = $this->getLogTime(); 
        $error = "{$e->getMessage()}\n\tThrown at: {$e->getFile()}:{$e->getLine()}\n\tStack:\n";
        foreach ($e->getTrace() as $stack => $trace) {
            $error .= "\tat #{$stack}: {$trace['file']}:{$trace['line']} {$trace['function']}(";
            foreach ($trace['args'] as $argNo => $arg) {
                if (is_array($arg)) {
                    $error .= 'Array, ';
                } else if (is_object($arg)) {
                    $error .= get_class($arg) . ', ';
                } else if (is_string($arg)) {
                    $error .= "'{$arg}', ";
                } else {
                    $error .= "{$arg}, ";
                }
            }
            $error = rtrim($error, ', ');
            $error .= ")\n";
        }

        fwrite(
            $this->logHandle,
            "[{$time}] {$level} {$error}\n"
        );
    }

    /**
     * Stops logging
     * 
     * @return void
     */
    public function end(): void
    {
        if ($this->logHandle) {
            fclose($this->logHandle);
        }
    }

    /**
     * Gets the current time formatted
     * 
     * @return string
     */
    private function getLogTime(): string
    {
        $date = new \DateTime();
        return $date->format('Y-m-d H:i:s');
    }
}
