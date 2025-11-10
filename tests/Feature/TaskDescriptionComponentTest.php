<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TaskDescriptionComponentTest extends TestCase
{
    public function test_long_description_renders_toggle_button(): void
    {
        $text = str_repeat('Descripción extensa. ', 10);

        $html = Blade::render('<x-task-description :text="$text" :limit="50" task-id="42" />', [
            'text' => $text,
        ]);

        $this->assertStringContainsString('Leer más', $html);
        $this->assertStringContainsString('aria-controls="task-desc-42-content"', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('clamp-resp', $html);
    }

    public function test_short_description_does_not_render_toggle_button(): void
    {
        $html = Blade::render('<x-task-description text="Breve" :limit="50" task-id="7" />');

        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringNotContainsString('aria-controls="task-desc-7-content"', $html);
    }
}
