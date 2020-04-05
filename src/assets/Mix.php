<?php

namespace iamntz\wpUtils\assets;

class Mix
{
    public function __construct($baseDir, $baseURL = null)
    {
        $this->baseDir = plugin_dir_path($baseDir);
        $this->baseURL = $baseURL;

        $this->prepareManifest();
    }

    protected function prepareManifest()
    {
        $manifestPath = $manifestPath ?? $this->baseDir . 'mix-manifest.json';

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

        return trailingslashit($this->hot ?? $this->baseURL) . $manifest[$fileName];
    }
}