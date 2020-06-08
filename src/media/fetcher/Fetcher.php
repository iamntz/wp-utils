<?php

namespace iamntz\wpUtils\media\fetcher;

class Fetcher
{
    private $postID;

    public function __construct($postID = null)
    {
        $this->postID = $postID;
    }

    public function fetch($url, $filename = null, $args = [])
    {
        add_filter('https_ssl_verify', '__return_false');

        $this->maybeRequireWpMediFiles();

        $fetchedFile = download_url($url);

        if (is_wp_error($fetchedFile)) {
            throw new \Exception($fetchedFile->get_error_message() . ": {$url}");
        }

        if (is_null($filename)) {
            $filename = uniqid(time() . true);
        } else {
            $filename = mb_substr($filename, 0, 100);
        }

        $extension = explode('.', $url);

        if (\count($extension) > 1) {
            $filename .= '.' . end($extension);
        }

        $return = $this->insertFileToMediaLibrary([
            'name' => $filename,
            'tmp_name' => $fetchedFile,
            'size' => filesize($fetchedFile),
        ]);

        @unlink($fetchedFile);

        return $return;
    }

    protected function maybeRequireWpMediFiles()
    {
        if (\function_exists('media_handle_upload')) {
            return;
        }

        require_once ABSPATH . 'wp-admin' . '/includes/image.php';
        require_once ABSPATH . 'wp-admin' . '/includes/file.php';
        require_once ABSPATH . 'wp-admin' . '/includes/media.php';
    }

    protected function insertFileToMediaLibrary($file)
    {
        $this->maybeRequireWpMediFiles();

        $uploadFile = media_handle_sideload($file, $this->postID);

        if (is_wp_error($uploadFile)) {
            $file = print_r($file, true);

            throw new \Exception($uploadFile->get_error_message() . ": {$file}");
        }

        $path = wp_upload_dir();

        $file['id'] = $uploadFile;
        $file['path'] = $path['path'] . \DIRECTORY_SEPARATOR . $file['name'];

        update_attached_file($file['id'], $file['path']);
        $meta = wp_generate_attachment_metadata($file['id'], $file['path']);

        if (is_wp_error($meta)) {
            throw new \Exception($meta->get_error_message());
        }

        wp_update_attachment_metadata($file['id'], $meta);

        return [
            'file' => $file,
            'fileID' => $uploadFile,
            'mime' => get_post_mime_type($uploadFile),
        ];
    }
}
