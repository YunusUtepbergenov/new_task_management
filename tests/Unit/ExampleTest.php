<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_sidebar_menu_click_handler_is_namespaced_and_deduplicated(): void
    {
        $scriptPath = dirname(__DIR__, 2).'/public/assets/js/app.js';
        $scriptContent = file_get_contents($scriptPath);

        $this->assertIsString($scriptContent);
        $this->assertStringContainsString("off('click.sidemenu', 'a')", $scriptContent);
        $this->assertStringContainsString("on('click.sidemenu', 'a'", $scriptContent);
    }

    public function test_layout_vendor_scripts_are_loaded_once_for_livewire_navigation(): void
    {
        $layoutPath = dirname(__DIR__, 2).'/resources/views/layouts/main.blade.php';
        $layoutContent = file_get_contents($layoutPath);

        $this->assertIsString($layoutContent);
        $this->assertStringContainsString('jquery-3.5.1.min.js', $layoutContent);
        $this->assertStringContainsString('data-navigate-once></script>', $layoutContent);
    }

    public function test_main_layout_uses_vite_for_app_javascript(): void
    {
        $layoutPath = dirname(__DIR__, 2).'/resources/views/layouts/main.blade.php';
        $layoutContent = file_get_contents($layoutPath);

        $this->assertIsString($layoutContent);
        $this->assertStringContainsString("@vite('resources/js/app.js')", $layoutContent);
        $this->assertStringNotContainsString("asset('js/app.js')", $layoutContent);
    }

    public function test_package_json_uses_vite_scripts(): void
    {
        $packageJsonPath = dirname(__DIR__, 2).'/package.json';
        $packageJsonContent = file_get_contents($packageJsonPath);

        $this->assertIsString($packageJsonContent);

        $package = json_decode($packageJsonContent, true);

        $this->assertIsArray($package);
        $this->assertSame('vite', $package['scripts']['dev']);
        $this->assertSame('vite build', $package['scripts']['build']);
    }

    public function test_vite_config_exists_with_laravel_plugin_js_entrypoint(): void
    {
        $viteConfigPath = dirname(__DIR__, 2).'/vite.config.js';
        $viteConfigContent = file_get_contents($viteConfigPath);

        $this->assertIsString($viteConfigContent);
        $this->assertStringContainsString("import laravel from 'laravel-vite-plugin';", $viteConfigContent);
        $this->assertStringContainsString("input: ['resources/js/app.js']", $viteConfigContent);
    }
}
