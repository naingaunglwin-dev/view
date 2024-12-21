<?php

/**
 * @package NAL\View
 * @copyright NaingAungLwin
 * @license MIT
 * @link https://github.com/naingaunglwin-dev/view
 */

namespace NAL\View;

use NAL\View\Exception\PathNotFound;

class View
{
    /**
     * Directory separator for parsing view paths.
     *
     * @var string
     */
    private string $directorySeparator = ">";

    /**
     * The parent template to extend.
     *
     * @var string
     */
    private string $extend = '';

    /**
     * Sections content.
     *
     * @var array
     */
    private array $sections = [];

    /**
     * Stack of active sections.
     *
     * @var array
     */
    private array $sectionStacks = [];

    /**
     * Constructor for the View class.
     *
     * @param string|null $path The base path for view files.
     * @param string|object|null $engine Custom view rendering engine.
     */
    public function __construct(
        private ?string $path = null,
        private null|string|object $engine = null
    )
    {
        $this->initPath();

        $this->initEngine();
    }

    /**
     * Initializes the view path, ensuring it exists and is properly formatted.
     *
     * @return void
     */
    private function initPath(): void
    {
        if (empty($this->path)) $this->path = dirname(__DIR__, 3);

        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $this->path = $this->parseDir($this->path);

        if (!str_ends_with($this->path, DIRECTORY_SEPARATOR)) {
            $this->path .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Initializes the custom rendering engine if provided.
     *
     * @return void
     * @throws \InvalidArgumentException If the engine class does not exist.
     */
    private function initEngine(): void
    {
        if ($this->engine && is_string($this->engine)) {
            if (!class_exists($this->engine)) {
                throw new \InvalidArgumentException("Undefined View engine [{$this->engine}]");
            }

            $this->engine = new $this->engine();
        }
    }

    /**
     * Renders a view file or an array of views.
     *
     * @param string|array $view The view file(s) to render.
     * @param array $data The data to pass to the view.
     * @param bool $output Whether to output the rendered view directly.
     * @return string|null The rendered content or null if output is true.
     * @throws PathNotFound If a view file is not found.
     */
    public function render(string|array $view, array $data = [], bool $output = true): ?string
    {
        $parsed = $this->parse($view);

        if ($this->engine && method_exists($this->engine, 'render')) {
            return $this->engine->render($this->parse($view, false), $data);
        }

        $render = $this->renderView($parsed, $data);

        if ($this->extend) {
            $parentView = new static($this->path, $this->engine);
            $parentView->sections = $this->sections;
            $parent = $parentView->render($this->extend, $data, false);

            $render = count($parsed) > 1 ? $render .  $parent : $parent;
        }

        if ($output) echo $render;

        return $render;
    }

    /**
     * Renders the view file(s) with the provided data.
     *
     * @param array $views List of parsed view paths.
     * @param array $data Data to pass to the view.
     * @return string Rendered content.
     */
    private function renderView(array $views, array $data): string
    {
        ob_start();

        if (!empty($data)) {
            extract($data, EXTR_SKIP);
        }

        foreach ($views as $view) {
            include $view;
        }

        return ob_get_clean() ?: '';
    }

    /**
     * Parses and validates view paths.
     *
     * @param string|array $views The view path(s).
     * @param bool $appendParentDir Whether to append the base directory to the view paths.
     * @return array List of valid view paths.
     * @throws PathNotFound If a view file does not exist.
     */
    private function parse(string|array $views, bool $appendParentDir = true): array
    {
        $parsed = [];

        if (is_string($views)) {
            $views = [$views];
        }

        foreach ($views as $view) {
            $view = $this->sanitizeViewPath($view);

            if ($appendParentDir) {
                if (!file_exists($view)) {
                    throw new PathNotFound("{$view} does not exist");
                }
            }

            $parsed[] = $view;
        }

        return $parsed;
    }

    /**
     * Sanitizes and validates a single view path.
     *
     * @param string $view The raw view path.
     * @return string The sanitized view path.
     * @throws PathNotFound If the path is invalid.
     */
    private function sanitizeViewPath(string $view): string
    {
        $view = str_replace("\0", '', $view);

        $view = preg_replace(
            "/\s+/", "", trim($view)
        );

        if (!pathinfo($view, PATHINFO_EXTENSION)) {
            $view .= '.php';
        }

        $view = realpath($this->path . DIRECTORY_SEPARATOR . $this->parseDir($view));

        if (!str_starts_with($view, $this->path)) {
            throw new PathNotFound("Invalid view path: {$view}");
        }

        return $view;
    }

    /**
     * Normalizes directory separators in a path.
     *
     * @param string $path The raw path.
     * @return string The normalized path.
     */
    private function parseDir(string $path): string
    {
        return str_replace(
            [
                $this->directorySeparator,
                "/", "\\"
            ],
            DIRECTORY_SEPARATOR, $path
        );
    }

    /**
     * Sets the parent template to extend.
     *
     * @param string $template The parent template path.
     * @return void
     */
    public function extend(string $template): void
    {
        $this->extend = $this->parseDir($template);
    }

    /**
     * Starts a new content section.
     *
     * @param string $section The section name.
     * @return void
     */
    public function section(string $section): void
    {
        $this->sectionStacks[] = $section;

        if (!isset($this->sections[$section])) {
            $this->sections[$section] = '';
        }

        ob_start();
    }

    /**
     * Ends the current content section.
     *
     * @return void
     * @throws \BadMethodCallException If there is no active section to end.
     */
    public function end(): void
    {
        if (empty($this->sectionStacks)) {
            throw new \BadMethodCallException("No active section to end");
        }

        $section = array_pop($this->sectionStacks);
        $content = ob_get_clean();

        $this->sections[$section] .= $content;
    }

    /**
     * Outputs the content of a section.
     *
     * @param string $section The section name.
     * @return void
     */
    public function yield(string $section): void
    {
        echo $this->sections[$section] ?? '';
    }
}
