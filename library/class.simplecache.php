<?php

namespace Psr\SimpleCache;

if (!interface_exists('\Psr\SimpleCache\CacheInterface')) {
    interface CacheInterface
    {
        public function get($key, $default = null);
        public function set($key, $value, $ttl = null);
        public function delete($key);
        public function clear();
        public function getMultiple($keys, $default = null);
        public function setMultiple($values, $ttl = null);
        public function deleteMultiple($keys);
        public function has($key);
    }
}

namespace FeedPiper\Cache;

use Psr\SimpleCache\CacheInterface;

class FileCache implements CacheInterface
{
    private $cacheDir;
    private $defaultTtl;

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($cacheDir, '/\\') . DIRECTORY_SEPARATOR;
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    private function getFilePath(string $key): string
    {
        // SimplePie passes URLs or cache keys which might have special characters
        return $this->cacheDir . md5($key) . '.cache';
    }

    public function get($key, $default = null)
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if ($content !== false) {
                $data = @unserialize($content);
                if ($data !== false && isset($data['expires']) && $data['expires'] > time()) {
                    return $data['value'];
                }
            }
            // Expired or invalid
            $this->delete($key);
        }
        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        $file = $this->getFilePath($key);
        $expires = time() + ($ttl !== null ? (int) $ttl : $this->defaultTtl);
        $data = serialize(['value' => $value, 'expires' => $expires]);
        return file_put_contents($file, $data, LOCK_EX) !== false;
    }

    public function delete($key)
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public function clear()
    {
        $success = true;
        foreach (glob($this->cacheDir . '*.cache') as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }
        return $success;
    }

    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMultiple($keys)
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function has($key)
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if ($content !== false) {
                $data = @unserialize($content);
                if ($data !== false && isset($data['expires']) && $data['expires'] > time()) {
                    return true;
                }
            }
            $this->delete($key);
        }
        return false;
    }
}
