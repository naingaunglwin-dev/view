<?php

namespace NAL\View;

use InvalidArgumentException;
use NAL\View\Helper\File;

class View
{
    private string $path;

    public function __construct(string $viewPath)
    {
        $viewPath = trim($viewPath);

        if (!is_dir($viewPath)) {
            throw new InvalidArgumentException("Invalid directory : $viewPath");
        }

        if (!str_ends_with($viewPath, DIRECTORY_SEPARATOR)) {
            $viewPath = $viewPath . DIRECTORY_SEPARATOR;
        }

        $this->path = $viewPath;
    }

    /**
     * Renders the specified view file(s) with associated data.
     *
     * @param string|array $view The view file(s) to render.
     * @param array|null $data The associated data for the view(s).
     * @param bool|null $return Whether to return the rendered output or output it directly.
     *
     * @return string|null The rendered output if $return is true, null otherwise.
     */
    public function render(string|array $view, array $data = null, bool $return = null): ?string
    {
        if ($return === null) {
            $return = false;
        }

        $files = [];

        if (is_string($view)) {
            $files[] = $view;
        } else {
            $files = $view;
        }

        $filtered_views = [];

        foreach ($files as $each) {
            $each = trim($each);

            if (str_starts_with($each, '/' || '\\')) {
                $each = substr($each, 1);
            }

            $checkedView = new File($this->path . $each);

            $extension = $checkedView->getExtension();

            if (empty($extension)) {
                $each = $each . '.php';
            }

            $checkedView = new File($this->path . $each);

            if (!$checkedView->isExist()) {
                exit(sprintf("file not found : %s", $this->path . $each));
            }

            $filtered_views[] = $this->path . $each;
        }

        $output = "<!-- Output View Start -->\n";

        $output .= (function ($files, $data) {
            ob_start();

            foreach ($files as $file) {
                if (!empty($data)) {
                    extract($data);
                }

                require_once $file;
            }

            return ob_get_clean();
        })($filtered_views, $data);

        $output .= "\n<!-- Output View End -->\n";

        if ($return === false) {
            echo $output;
        }

        return $output;
    }
}
