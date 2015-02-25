<?php namespace CodeZero\Filesystem;

interface Filesystem
{
    /**
     * Check if a path exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path);

    /**
     * Check if a file exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile($file);

    /**
     * Check if a symbolic link exists
     *
     * @param string $link
     *
     * @return bool
     */
    public function isSymLink($link);

    /**
     * Check if a directory exists
     *
     * @param string $dir
     *
     * @return bool
     */
    public function isDirectory($dir);

    /**
     * Check if a file or directory is readable
     *
     * @param string $path
     *
     * @return bool
     */
    public function isReadable($path);

    /**
     * Check if a file or directory is writable
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable($path);

    /**
     * Check if a file is executable
     *
     * @param string $file
     *
     * @return bool
     */
    public function isExecutable($file);

    /**
     * Get the parent directory of a file or directory
     *
     * @param string $path
     *
     * @return string
     */
    public function getParentDirectory($path);

    /**
     * Check if a directory is empty
     *
     * @param string $dir
     *
     * @return bool
     * @throws IOException
     */
    public function isEmpty($dir);

    /**
     * List the files and directories in a directory
     *
     * @param string $dir
     *
     * @return array
     * @throws IOException
     */
    public function listDirectory($dir);

    /**
     * Read the contents of a file
     *
     * @param string $file
     *
     * @return string
     * @throws IOException
     */
    public function readFile($file);

    /**
     * Change permissions of a file or directory
     *
     * @param string $path
     * @param int $chmod
     *
     * @return bool
     */
    public function chmod($path, $chmod);

    /**
     * Create a directory
     *
     * @param string $path
     * @param int $chmod
     * @param bool $recursive
     *
     * @return bool
     * @throws IOException
     */
    public function createDirectory($path, $chmod = 0755, $recursive = true);

    /**
     * Create a file
     *
     * @param string $path
     * @param string $data
     * @param bool $overwrite
     *
     * @return int
     * @throws IOException
     */
    public function createFile($path, $data, $overwrite = false);

    /**
     * Delete a file or directory
     *
     * @param string $path
     * @param bool $recursive
     *
     * @return bool
     * @throws IOException
     */
    public function delete($path, $recursive = false);

    /**
     * Rename a file or directory
     *
     * @param string $src
     * @param string $dest
     * @param bool $overwrite
     *
     * @return bool
     * @throws IOException
     */
    public function rename($src, $dest, $overwrite = false);

    /**
     * Copy a file or directory
     *
     * @param string $src
     * @param string $dest
     * @param bool $overwrite
     *
     * @return bool
     * @throws IOException
     */
    public function copy($src, $dest, $overwrite = false);
}
