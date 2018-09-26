<?php

namespace Acquia\Orca\Robo\Plugin\Commands;

use Acquia\Orca\Exception\FixtureNotReadyException;
use Robo\Result;

/**
 * Provides the "fixture:reset" command.
 */
class FixtureResetCommand extends CommandBase {

  /**
   * The collection to return.
   *
   * @var \Robo\Collection\CollectionBuilder
   */
  private $collection;

  /**
   * Resets the test fixture to its base state and optionally reinstalls Drupal.
   *
   * @command fixture:reset
   * @option reset-code Reset the codebase. All uncommitted code changes will be
   *   lost! Committed changes will be saved to a timestamped backup branch.
   * @option empty-database Empty the Drupal database.
   * @option reinstall-drupal Reinstall Drupal.
   * @option all Resets the code, empties the database, and reinstalls Drupal.
   *   Takes precedence over other options.
   * @aliases reset
   *
   * @return \Robo\Collection\CollectionBuilder|int
   */
  public function execute(array $options = [
    'reset-code|c' => FALSE,
    'empty-database|e' => FALSE,
    'reinstall-drupal|r' => FALSE,
    'all|a' => FALSE,
  ]) {
    if (!file_exists($this->buildPath())) {
      throw new FixtureNotReadyException();
    }

    if (!$options['all'] && !$options['reset-code'] && !$options['empty-database'] && !$options['reinstall-drupal']) {
      $this->say('Nothing to do. Use a command option. See `orca help fixture:reset`.');
      return Result::EXITCODE_ERROR;
    }

    $confirm = $this->confirm('Are you sure you want to reset the test fixture?');
    if (!$confirm && !$options['no-interaction']) {
      return Result::EXITCODE_USER_CANCEL;
    }

    $this->collection = $this->collectionBuilder();

    if ($options['all'] || $options['reset-code']) {
      $this->resetAndBackupCode();
    }

    // Reinstalling Drupal already drops all tables in the database, so there's
    // never a reason to do so here AND reinstall Drupal.
    if ($options['all'] || $options['reinstall-drupal']) {
      $this->collection->addTask($this->taskInstallDrupal());
    }
    elseif ($options['empty-database']) {
      $this->collection->addTask($this->taskEmptyDrupalDatabase());
    }

    return $this->collection;
  }

  /**
   * Resets and backs up the build code.
   */
  protected function resetAndBackupCode() {
    $git = $this->taskGitStack()
      ->dir($this->buildPath());
    $this->collection
      ->addTaskList([
        $git->exec(sprintf('branch backup-%s', date('Y-m-d-Gis'))),
        $this->taskFixFilePermissions(),
        $git->exec(sprintf('reset --hard %s', self::BASE_FIXTURE_BRANCH)),
        $git->exec('clean -fd'),
      ]);
  }

}