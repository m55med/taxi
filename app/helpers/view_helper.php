<?php

/**
 * Renders a partial view and passes data to it.
 * This ensures that the passed variables are correctly scoped within the included file.
 *
 * @param string $path The path to the partial file from the `app/views` directory.
 * @param array $data The data to extract into variables for the partial view.
 */
function render_partial(string $path, array $data = [])
{
    extract($data);
    include __DIR__ . '/../views/' . $path;
} 