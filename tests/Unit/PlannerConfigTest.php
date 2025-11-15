<?php

namespace Tests\Unit;

use Tests\TestCase;

class PlannerConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->clearPlannerEnv();

        parent::tearDown();
    }

    /**
     * @dataProvider disablingValuesProvider
     */
    public function test_planner_anchors_can_be_disabled_via_environment(string $value): void
    {
        $this->setPlannerEnv($value);

        $config = $this->loadPlannerConfig();

        $this->assertFalse($config['anchors']['enabled']);
    }

    public function test_planner_anchors_enabled_by_default(): void
    {
        $this->clearPlannerEnv();

        $config = $this->loadPlannerConfig();

        $this->assertTrue($config['anchors']['enabled']);
    }

    public static function disablingValuesProvider(): array
    {
        return [
            ['false'],
            ['0'],
            ['off'],
        ];
    }

    private function loadPlannerConfig(): array
    {
        return require base_path('config/planner.php');
    }

    private function setPlannerEnv(string $value): void
    {
        putenv("PLANNER_ANCHORS={$value}");
        $_ENV['PLANNER_ANCHORS'] = $value;
        $_SERVER['PLANNER_ANCHORS'] = $value;
    }

    private function clearPlannerEnv(): void
    {
        putenv('PLANNER_ANCHORS');
        unset($_ENV['PLANNER_ANCHORS'], $_SERVER['PLANNER_ANCHORS']);
    }
}
