<?php

if (!function_exists('empty_dir')) {
    /**
     * Determine if directory is empty.
     *
     * @param string $dir The path to directory
     *
     * @return bool
     */
    function empty_dir($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }

        return 2 == count(scandir($dir));
    }
}
