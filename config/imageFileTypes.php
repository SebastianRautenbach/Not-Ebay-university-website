<?php // here we can whitelist any of the file types we want, this is ovibouisly for security concerns
$image_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];

function rearange_files(&$extra_images)
{
    $is_multiple = is_array($extra_images['name']);
    $file_count = $is_multiple ? count($extra_images['name']) : 1;
    $file_keys = array_keys($extra_images);

    $reconstructed_arr = [];

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            if ($is_multiple) {
                $reconstructed_arr[$i][$key] = $extra_images[$key][$i];
            } else {
                $reconstructed_arr[$i][$key] = $extra_images[$key];
            }
        }
    }
    return $reconstructed_arr;
}
