<?php

namespace NALViewTest;

use NAL\View\View;
use NAL\View\Exception\PathNotFound;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/view_testcase';

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*'));
            rmdir($this->tempDir);
        }
    }

    public function testInitPathCreatesDirectoryIfNotExists(): void
    {
        $view = new View($this->tempDir);

        $this->assertDirectoryExists($this->tempDir);
    }

    public function testInitEngineWithInvalidEngineThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined View engine');

        new View(null, 'NonExistentEngine');
    }

    public function testRenderThrowsExceptionForNonExistentView(): void
    {
        $view = new View($this->tempDir);
        $this->expectException(PathNotFound::class);

        $view->render('nonexistent_view');
    }

    public function testRenderRendersView(): void
    {
        $filePath = $this->tempDir . '/test_view.php';
        file_put_contents($filePath, '<?php echo "Hello, " . $name; ?>');

        $view = new View($this->tempDir);
        $output = $view->render('test_view', ['name' => 'World'], false);

        $this->assertEquals('Hello, World', $output);
    }

    public function testSanitizeViewPathValidatesPath(): void
    {
        $view = new View($this->tempDir);

        $filePath = $this->tempDir . '/valid_view.php';
        file_put_contents($filePath, 'Test');

        $method = new \ReflectionMethod(View::class, 'sanitizeViewPath');
        $method->setAccessible(true);

        $sanitizedPath = $method->invoke($view, 'valid_view');

        $this->assertEquals('valid_view.php', $sanitizedPath);
    }

    public function testExtendMethodSetsParentTemplate(): void
    {
        $view = new View($this->tempDir);
        $view->extend('parent_template');

        $reflection = new \ReflectionClass($view);
        $property = $reflection->getProperty('extend');
        $property->setAccessible(true);

        $this->assertEquals('parent_template', $property->getValue($view));
    }

    public function testSectionsHandling(): void
    {
        $view = new View($this->tempDir);

        $view->section('header');
        echo 'Header Content';
        $view->end();

        ob_start();
        $view->yield('header');
        $output = ob_get_clean();

        $this->assertEquals('Header Content', $output);
    }

    public function testEndThrowsExceptionWhenNoSection(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No active section to end');

        $view = new View($this->tempDir);
        $view->end();
    }

    public function testCustomRendererEngine()
    {
        $filePath = $this->tempDir . '/test_custom_engine_view.php';
        file_put_contents($filePath, '<?php echo "Hello, " . $name; ?>');

        $view = new View(
            $this->tempDir,
            CustomEngine::class,
            true
        );

        $output = $view->render('test_custom_engine_view', ['name' => 'World'], false); // this is render method from custom engine

        $this->assertEquals('Hello, World', $output);
    }

    public function testRenderWithExtend(): void
    {
        $childFilePath = $this->tempDir . '/child_view.php';
        file_put_contents(
            $childFilePath,
            '<?php $this->section("content"); echo "Child Content"; $this->end(); ?>'
        );

        $parentFilePath = $this->tempDir . '/parent_view.php';
        file_put_contents(
            $parentFilePath,
            '<html><body><?php $this->yield("content"); ?></body></html>'
        );

        $view = new View($this->tempDir);
        $view->extend('parent_view');

        $output = $view->render('child_view', [], false);

        $expectedOutput = '<html><body>Child Content</body></html>';
        $this->assertEquals($expectedOutput, $output);
    }

    public function testInitPathCreatesNonExistentDirectory(): void
    {
        $nonExistentPath = $this->tempDir . '/non_existent_dir';

        if (is_dir($nonExistentPath)) {
            rmdir($nonExistentPath);
        }

        $view = new View($nonExistentPath);

        $this->assertDirectoryExists($nonExistentPath);

        rmdir($nonExistentPath);
    }
}
