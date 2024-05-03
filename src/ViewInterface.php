<?php

namespace NAL\View;

use BadMethodCallException;

interface ViewInterface
{
    /**
     * Renders the specified view(s).
     *
     * @param string|array $view   The path to a single view or an array of view paths.
     * @param array|null   $data   An associative array of data to be passed to the view(s).
     * @param bool         $return Whether to return the rendered view(s) as a string.
     *
     * @return string|null The rendered view(s), or null if $return is true.
     */
    public function render(string|array $view, array $data = null, bool $return = false): ?string;

    /**
     * Starts a new section.
     *
     * @param string $name The name of the section.
     *
     * @return void
     *
     * @throws BadMethodCallException
     */
    public function section(string $name): void;

    /**
     * Ends a section and captures its content.
     *
     * @param string $name The name of the section.
     *
     * @return void
     *
     * @throws BadMethodCallException
     */
    public function endSection(string $name): void;

    /**
     * Sets the view to be extended.
     *
     * @param string $file The path to the view file being extended.
     *
     * @return void
     *
     * @throws BadMethodCallException
     */
    public function extends(string $file): void;

    /**
     * Displays the content of a section.
     *
     * @param string $name The name of the section.
     *
     * @return string|null The content of the section, or null if the section does not exist.
     */
    public function displaySection(string $name): ?string;

    /**
     * Clean view properties
     *
     * @param bool $outputOnly To determine that clean only the output buffers or need to clean views also
     *
     * @return void
     */
    public function clean(bool $outputOnly = true): void;
}
