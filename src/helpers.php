<?php 

if (! function_exists('ck_url')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function ck_url($uri)
    {
        return app('cloudKilat')->Url()->generate($uri);
    }
}