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

if (!function_exists('unify_separator')) {
    /**
     * Unify the separators of the path.
     *
     * @param string $path      The path need to format
     * @param string $separator The directory separator
     *
     * @return string
     */
    function unify_separator($path, $separator = DIRECTORY_SEPARATOR)
    {
        return str_replace(['/', '\\'], $separator, $path);
    }
}

if (!function_exists('absolute_path')) {
    /**
     * Return absolute path from input path.
     * This function is an alternative to realpath() function for non-existent paths.
     *
     * @param string $path      The input path
     * @param string $separator The directory separator wants to use in the results
     *
     * @return string
     */
    function absolute_path($path, $separator = DIRECTORY_SEPARATOR)
    {
        // Normalize directory separators
        $path = str_replace(['/', '\\'], $separator, $path);

        // Store root part of path
        $root = null;
        while (is_null($root)) {
            // Check if path start with a separator (UNIX)
            if (substr($path, 0, 1) === $separator) {
                $root = $separator;
                $path = substr($path, 1);
                break;
            }

            // Check if path start with drive letter (WINDOWS)
            preg_match('/^[a-z]:/i', $path, $matches);
            if (isset($matches[0])) {
                $root = $matches[0] . $separator;
                $path = substr($path, 2);
                break;
            }

            $path = getcwd() . $separator . $path;
        }

        // Get and filter empty sub paths
        $subPaths = array_filter(explode($separator, $path), 'strlen');

        $absolutes = [];
        foreach ($subPaths as $subPath) {
            if ('.' === $subPath) {
                continue;
            }

            if ('..' === $subPath) {
                array_pop($absolutes);
                continue;
            }

            $absolutes[] = $subPath;
        }

        return $root . implode($separator, $absolutes);
    }
}

if (!function_exists('relative_path')) {
    /**
     * Return relative path from source directory to destination.
     *
     * @param string $from      The path of source directory
     * @param string $to        The path of file or directory to be compare
     * @param string $separator The directory separator wants to use in the results
     *
     * @return string
     */
    function relative_path($from, $to, $separator = DIRECTORY_SEPARATOR)
    {
        $fromParts  = explode($separator, absolute_path($from, $separator));
        $toParts    = explode($separator, absolute_path($to, $separator));
        $diffFromTo = array_diff($fromParts, $toParts);
        $diffToFrom = array_diff($toParts, $fromParts);

        if ($diffToFrom === $toParts) {
            return implode($separator, $toParts);
        }

        return str_repeat('..' . $separator, count($diffFromTo)) . implode($separator, $diffToFrom);
    }
}
