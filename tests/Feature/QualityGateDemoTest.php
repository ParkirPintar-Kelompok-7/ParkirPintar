<?php

namespace Tests\Feature;

use Tests\TestCase;

class QualityGateDemoTest extends TestCase
{
    public function test_quality_gate_blocks_deployment(): void
    {
        $this->assertTrue(
            false,
            'Simulasi kegagalan untuk membuktikan Quality Gate Jenkins'
        );
    }
}
