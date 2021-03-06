<?php

namespace Acquia\Orca\Task\StaticAnalysisTool;

use Acquia\Orca\Task\TaskBase;

/**
 * Detects potential problems in PHP source code.
 */
class PhpMessDetectorTask extends TaskBase {

  /**
   * {@inheritdoc}
   */
  public function statusMessage(): string {
    return 'Looking for potential problems in PHP source code';
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $this->overrideConfig();
    $this->runPhpmd();
    $this->restoreConfig();
  }

  /**
   * Runs phpmd.
   */
  public function runPhpmd(): void {
    $this->processRunner->runOrcaVendorBin([
      'phpmd',
      '.',
      'text',
      "{$this->projectDir}/phpmd.xml.dist",
      // Emit output but don't fail builds.
      '--ignore-violations-on-exit',
    ], $this->getPath());
  }

  /**
   * Overrides the active configuration.
   */
  public function overrideConfig(): void {
    $this->configFileOverrider->setPaths(
      "{$this->projectDir}/phpmd.xml.dist",
      "{$this->getPath()}/phpmd.xml"
    );
    $this->configFileOverrider->override();
  }

  /**
   * Restores the previous configuration.
   */
  public function restoreConfig(): void {
    $this->configFileOverrider->restore();
  }

}
