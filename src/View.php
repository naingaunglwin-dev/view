<?php

namespace NAL\View;

use BadMethodCallException;
use NAL\View\Exception\PathNotFound;

class View implements ViewInterface
{
    /**
     * The base path for views.
     *
     * @var string
     */
    private string $path;

    /**
     * An array containing the paths to individual views.
     *
     * @var array
     */
    private array $views = [];

    /**
     * An array containing temporary views used during rendering.
     *
     * @var array
     */
    private array $temp = [];

    /**
     * An associative array containing sections and their content.
     *
     * @var array
     */
    private static array $sections = [];

    /**
     * The path to the views being extended.
     *
     * @var string
     */
    private static string $extend = '';

    /**
     * An array containing the views to be rendered.
     *
     * @var array
     */
    private array $render = [];

    /**
     * A flag indicating whether a section is being rendered.
     *
     * @var bool
     */
    private bool $isSection = false;

    public function __construct(string $path)
    {
        $path = trim($path ?? dirname(__DIR__) . '../../');

        // Check if $path is not empty and end with '/' or not
        if ($path !== '' && !empty($path) && !str_ends_with($path, DIRECTORY_SEPARATOR)) {
            $path = $path . DIRECTORY_SEPARATOR;
        }

        $this->path = $path;
    }

    /**
     * Sets the views or views to be rendered.
     *
     * @param string|array $view The path to a single views or an array of views paths.
     *
     * @throws BadMethodCallException
     */
    private function setView(string|array $view): void
    {
        $files = [];

        if (is_string($view)) {
            $files[] = $view;
        } else {
            $files = $view;
        }

        $filtered_views = [];

        foreach ($files as $file) {
            $file = trim($file);

            if (str_starts_with($file, '*')) {
                $file = substr($file, 1);
            }

            $file = str_replace('*', '/', $file);

            $file = str_replace($this->path, '', $file);

            $file = $this->verifyExtension($this->path . $file);

            if (!$this->isExists($file)) {
                throw new PathNotFound($file, 404);
            }

            $filtered_views[] = $file;
        }

        $this->views = $filtered_views;
    }

    /**
     * Renders and captures the output of the specified views.
     *
     * @param array $views The path to an array of views paths.
     * @param array|null $data  An associative array of data to be passed to the views(s).
     *
     * @return string|bool The rendered views(s) output, or false on failure.
     */
    private function getViews(array $views, ?array $data): bool|string
    {
        // Save views in local properties
        // to prevent variable name conflict from $data when extract
        $this->temp = $views;

        if ($data !== null) {
            extract($data, EXTR_SKIP);
        }

        ob_start();

        foreach ($this->temp as $view) {
            include $view;
        }

        $content = ob_get_clean();

        // Clear the temp views
        $this->temp = [];

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function render(string|array $view, array $data = null, bool $return = false): ?string
    {
        //var_dump($views);
        $this->setView($view);

        // Prepare views to render
        $this->prepare();

        // Get included views
        $result = $this->getViews($this->render, $data);

        if ($this->isSection && !empty(self::$sections)) {
            // Render the sections
            $this->isSection = false;

            // Re-prepare the views in case sections are extended
            $this->prepare();

            // Get included views
            $result = $this->getViews($this->render, $data);
        }

        if ($return === false) {
            // echo the output if return false
            echo $result;
        }

        return $result;
    }

    /**
     * Prepares the views for rendering.
     *
     * @return void
     */
    private function prepare(): void
    {
        $files = [];

        foreach ($this->views as $view) {
            $content = file_get_contents($view);

            if ($content !== false && preg_match('/\$this->extends\(\'(.*?)\'\);/s', $content, $matches)) {
                $files[] = $this->verifyExtension($this->path . $matches[1]);
            }
            $files[] = $view;
        }

        $this->render = $files;

        $this->render = array_unique($this->render);
    }

    /**
     * @inheritDoc
     */
    public function section(string $name): void
    {
        if (empty(self::$extend)) {
            throw new BadMethodCallException("No extend method defined");
        }

        $this->isSection = true;

        self::$sections[$name] = [
            'content' => '',
            'extend'  => self::$extend,
        ];

        ob_start();
    }

    /**
     * @inheritDoc
     */
    public function endSection(string $name): void
    {
        if (!isset(self::$sections[$name])) {
            throw new BadMethodCallException('Section "' . $name . '" does not exist');
        }

        $content = ob_get_clean();

        self::$sections[$name]['content'] = $content;
    }

    /**
     * @inheritDoc
     */
    public function extends(string $file): void
    {
        $file = trim($file);
        $file = str_replace('*', '/', $file);

        $file = $this->verifyExtension($this->path . $file);

        if (!$this->isExists($file)) {
            throw new PathNotFound($file, 404);
        }

        self::$extend = $file;
    }

    /**
     * @inheritDoc
     */
    public function displaySection(string $name): ?string
    {
        if (isset(self::$sections[$name])) {
            echo self::$sections[$name]['content'];
            return '';
        }

        return null;
    }

    /**
     * Verifies whether a views file exists.
     *
     * @param string $view The path to the views file.
     *
     * @return bool True if the views file exists, false otherwise.
     */
    private function isExists(string $view): bool
    {
        return file_exists($view);
    }

    /**
     * Verifies and adds the appropriate file extension to a views file.
     *
     * @param string $file The path to the views file.
     *
     * @return string The path to the views file with the appropriate extension.
     */
    private function verifyExtension(string $file): string
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (empty($extension)) {
            $file = $file . '.php';
        }

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function clean(bool $outputOnly = true): void
    {
        self::$extend   = '';
        self::$sections = [];

        if (!$outputOnly) {
            $this->views    = [];
        }
    }
}
