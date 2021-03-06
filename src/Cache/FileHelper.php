<?php

/**
 * This File is part of the Thapp\Jmg package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

namespace Thapp\Jmg\Cache;

use FilesystemIterator;

/**
 * @class FileHelper
 *
 * @package Thapp\Jmg
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 */
trait FileHelper
{
    /**
     * dumpFile
     *
     * @param string $file
     * @param string $contents
     *
     * @return bool
     */
    protected function dumpFile($file, $contents)
    {
        if (!$this->ensureDir($dir = dirname($file))) {
            return false;
        }

        if (!is_file($file)) {
            if (false === @touch($file)) {
                return false;
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'hlpr');

        file_put_contents($tmp, $contents);

        $source = fopen($tmp, 'r');
        $target = fopen($file, 'wb+');

        $result = stream_copy_to_stream($source, $target);

        fclose($source);
        fclose($target);

        unlink($tmp);

        return $result;
    }

    /**
     * ensureDir
     *
     * @param string $path
     *
     * @return bool
     */
    protected function ensureDir($path)
    {
        if (!is_dir($path)) {
            return mkdir($path, $this->mask(0775), true);
        }

        if (!is_writable($path)) {
            return chmod($path, $this->mask(0775));
        }

        return true;
    }

    /**
     * deleteDir
     *
     * @param string $dir
     *
     * @return bool
     */
    protected function deleteDir($dir)
    {
        if (!$this->sweepDir($dir)) {
            return false;
        }

        if (false !== @rmdir($dir)) {
            return !$this->isDir($dir);
        }

        return false;
    }

    /**
     * recursiveDelete
     *
     * @param string $dir
     *
     * @return bool
     */
    protected function sweepDir($dir)
    {
        if (!$this->isDir($dir)) {
            return false;
        }

        foreach (new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS) as $path => $item) {
            if ($item->isFile()) {
                unlink($item);
                continue;
            }

            if ($item->isDir()) {
                $this->deleteDir($path);
            }
        }

        return $this->isDir($dir);
    }

    /**
     * isFile
     *
     * @param string $path
     *
     * @return bool
     */
    public function isFile($path)
    {
        return is_file($path) && stream_is_local($path);
    }

    /**
     * isDir
     *
     * @param string $path
     *
     * @return bool
     */
    public function isDir($path)
    {
        return is_dir($path) && stream_is_local($path);
    }

    /**
     * exists
     *
     * @param string $file
     *
     * @return bool
     */
    public function exists($file)
    {
        return $this->isDir($file) || $this->isFile($file);
    }

    /**
     * get propper write mode
     *
     * @param int $mode
     *
     * @return int
     */
    private function mask($mode = 0775)
    {
        return 0775 & ~umask();
    }
}
