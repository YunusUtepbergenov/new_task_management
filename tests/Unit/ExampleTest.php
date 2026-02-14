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
}
