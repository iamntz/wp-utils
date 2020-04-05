<?php

namespace iamntz\wpUtils\assets;

class Mix
{
    public function __construct($baseDir, $baseURL = null)
    {
        $this->baseDir = trailingslashit($baseDir);
        $this->baseURL = trailingslashit($baseURL);

        $this->prepareManifest();
    }

    protected function prepareManifest()
    {
        $manifestPath = $this->baseDir . 'mix-manifest.json';

        if (!file_exists($manifestPath)) {
            throw new \Exception("mix-manifest.json is not readable on {$manifestPath}");
        }

        $this->manifest = json_decode(file_get_contents($manifestPath), true);

        $this->hot = file_exists($this->baseDir . 'hot') ? file_get_contents($this->baseDir . 'hot') : null;
    }

    public function mix($fileName)
    {
        if (empty($this->manifest[$fileName])) {
            throw new \Exception("{$fileName} is not defined in mix-manifest.json");
        }

        return ($this->hot ?? $this->baseURL) . $this->manifest[$fileName];
    }

    public function register($handle, $fileName, $deps = [], $extra = true)
    {
        $src = $this->mix($fileName);

        parse_str(parse_url($this->manifest[$fileName], PHP_URL_QUERY), $q);

        $version = $q['id'] ?? crc32($fileName);

        if (substr($fileName, -3) === '.js') {
            return wp_register_script($handle, $src, $deps, $version, $extra);
        }

        $media = !is_string($extra) || empty($extra) ? 'all' : $extra;

        return wp_register_style($handle, $src, $deps, $version, $media);
    }
}