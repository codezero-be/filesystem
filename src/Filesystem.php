<?php namespace CodeZero\Filesystem; 

class Filesystem
{
    /**
     * Check if a path exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Check if a file exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile($file)
    {
        return is_file($file);
    }

    /**
     * Check if a symbolic link exists
     *
     * @param string $link
     *
     * @return bool
     */
    public function isSymLink($link)
    {
        return is_link($link);
    }

    /**
     * Check if a directory exists
     *
     * @param string $dir
     *
     * @return bool
     */
    public function isDirectory($dir)
    {
        return is_dir($dir);
    }

    /**
     * Check if a file or directory is readable
     *
     * @param string $path
     *
     * @return bool
     */
    public function isReadable($path)
    {
        return @is_readable($path);
    }

    /**
     * Check if a file or directory is writable
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable($path)
    {
        return @is_writable($path);
    }

    /**
     * Check if a file is executable
     *
     * @param string $file
     *
     * @return bool
     */
    public function isExecutable($file)
    {
        return @is_executable($file);
    }

    /**
     * Get the parent directory of a file or directory
     *
     * @param string $path
     *
     * @return string
     */
    public function getParentDirectory($path)
    {
        return rtrim(dirname($path), './\\');
    }

    /**
     * Check if a directory is empty
     *
     * @param string $dir
     *
     * @return bool
     * @throws IOException
     */
    public function isEmpty($dir)
    {
        return count($this->listDirectory($dir)) == 0;
    }

    /**
     * List the files and directories in a directory
     *
     * @param string $dir
     *
     * @return array
     * @throws IOException
     */
    public function listDirectory($dir)
    {
        if ( ! $this->isDirectory($dir))
        {
            throw new IOException("The directory [{$dir}] doesn't exists.");
        }
        elseif (($listing = @scandir($dir)) === false)
        {
            throw new IOException("Could not get a directory listing of [{$dir}].");
        }

        unset($listing[array_search('.', $listing)]);
        unset($listing[array_search('..', $listing)]);

        sort($listing);

        return $listing;
    }

    /**
     * Read the contents of a file
     *
     * @param string $file
     *
     * @return string
     * @throws IOException
     */
    public function readFile($file)
    {
        if ( ! $this->exists($file))
        {
            throw new IOException("The file [{$file}] does not exist.");
        }
        elseif ( ! $this->isFile($file))
        {
            throw new IOException("The path [{$file}] exists, but is not a file.");
        }
        elseif (($data = @file_get_contents($file)) === false)
        {
            throw new IOException("The file [{$file}] could not be read.");
        }

        return $data;
    }

    /**
     * Change permissions of a file or directory
     *
     * @param string $path
     * @param int $chmod
     *
     * @return bool
     */
    public function chmod($path, $chmod)
    {
        return chmod($path, $chmod);
    }

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
    public function createDirectory($path, $chmod = 0755, $recursive = true)
    {
        if ($this->isDirectory($path))
        {
            return true;
        }
        elseif ($this->exists($path))
        {
            throw new IOException("The path [{$path}] exists but is not a directory.");
        }
        elseif (@mkdir($path, $chmod, $recursive) === false)
        {
            throw new IOException("The directory [{$path}] could not be created.");
        }

        return true;
    }

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
    public function createFile($path, $data, $overwrite = false)
    {
        if ($overwrite == false && $this->exists($path))
        {
            throw new IOException("The file [{$path}] already exists.");
        }

        $parent = $this->getParentDirectory($path);

        if ($parent && ! $this->exists($parent))
        {
            $this->createDirectory($parent);
        }
        elseif ($parent && ! $this->isDirectory($parent))
        {
            throw new IOException("Cannot write to [{$path}] because [{$parent}] is not a directory.");
        }

        if (($bytes = @file_put_contents($path, $data)) === false)
        {
            throw new IOException("The file [{$path}] could not be written.");
        }

        return $bytes ?: 0;
    }

    /**
     * Delete a file or directory
     *
     * @param string $path
     * @param bool $recursive
     *
     * @return bool
     * @throws IOException
     */
    public function delete($path, $recursive = false)
    {
        if ($this->isDirectory($path))
        {
            return $recursive
                ? $this->deleteDirectoryRecursive($path)
                : $this->deleteDirectory($path);
        }

        return $this->deleteFile($path);
    }

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
    public function rename($src, $dest, $overwrite = false)
    {
        if ($overwrite == false && $this->exists($dest))
        {
            throw new IOException("The file or directory [{$dest}] already exists.");
        }
        elseif (@rename($src, $dest) === false)
        {
            throw new IOException("The file or directory [{$src}] could not be renamed.");
        }

        return true;
    }

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
    public function copy($src, $dest, $overwrite = false)
    {
        return $this->isDirectory($src)
            ? $this->copyDirectoryRecursive($src, $dest, $overwrite)
            : $this->copyFile($src, $dest, $overwrite);
    }

    /**
     * Delete a directory recursively
     *
     * @param string $dir
     *
     * @return bool
     * @throws IOException
     */
    private function deleteDirectoryRecursive($dir)
    {
        $listing = $this->listDirectory($dir);

        foreach ($listing as $child)
        {
            $this->delete($dir.'/'.$child, true);
        }

        return $this->deleteDirectory($dir);
    }

    /**
     * Delete a directory
     *
     * @param string $dir
     *
     * @return bool
     * @throws IOException
     */
    private function deleteDirectory($dir)
    {
        if ( ! $this->isEmpty($dir))
        {
            throw new IOException("The directory [{$dir}] is not empty.");
        }
        elseif (rmdir($dir) === false)
        {
            throw new IOException("The directory [{$dir}] could not be deleted.");
        }

        return true;
    }

    /**
     * Delete a file
     *
     * @param string $file
     *
     * @return bool
     * @throws IOException
     */
    private function deleteFile($file)
    {
        if (unlink($file) === false)
        {
            throw new IOException("The file [{$file}] could not be deleted.");
        }

        return true;
    }

    /**
     * Copy a file
     *
     * @param string $src
     * @param string $dest
     * @param bool $overwrite
     *
     * @return bool
     * @throws IOException
     */
    private function copyFile($src, $dest, $overwrite)
    {
        if ( ! $this->isFile($src))
        {
            throw new IOException("The source path [{$src}] doesn't exist or is not a file.");
        }

        if ($this->isDirectory($dest))
        {
            $dest = $dest.'/'.basename($src);
        }

        if ($overwrite == false && $this->exists($dest))
        {
            throw new IOException("The file [{$dest}] already exists.");
        }

        if (($parent = $this->getParentDirectory($dest)) && ! $this->exists($parent))
        {
            $this->createDirectory($parent);
        }

        if (@copy($src, $dest) === false)
        {
            throw new IOException("The file [{$src}] could not be copied.");
        }

        return true;
    }

    /**
     * Copy a directory recursively
     *
     * @param string $src
     * @param string $dest
     * @param bool $overwrite
     *
     * @return bool
     * @throws IOException
     */
    private function copyDirectoryRecursive($src, $dest, $overwrite)
    {
        if ($overwrite == false && $this->exists($dest))
        {
            throw new IOException("The directory [{$dest}] already exists.");
        }

        $listing = $this->listDirectory($src);

        foreach ($listing as $child)
        {
            $this->copy($src.'/'.$child, $dest.'/'.$child, $overwrite);
        }

        return true;
    }
}
