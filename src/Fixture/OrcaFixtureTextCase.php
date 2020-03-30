<?php

namespace Acquia\Orca\Fixture;

use PHPUnit\Framework\TestCase;

class OrcaFixtureTextCase extends TestCase {
  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->execute('./bin/orca fixture:reset -f', getenv('ORCA_ROOT'));
  }
}