<?php

/**
 * The Toggl API token for the testing account.
 */
define('TOGGL_TEST_TOKEN', 'd37a6e2ebf3a5e2f0bb2a579725360f2');

// Include PHPUnit
require_once 'PHPUnit/Framework/TestCase.php';

// Include the Toggl class file.
require_once dirname(dirname(__FILE__)) . '/src/toggl.php';

class TogglTest extends PHPUnit_Framework_TestCase {
  protected $toggl;

  protected function setUp() {
    $this->toggl = new Toggl(TOGGL_TEST_TOKEN);
  }

  public function testConstruct() {
    $this->assertClassHasAttribute('token', 'Toggl');
    $this->assertSame($this->toggl->getToken(), TOGGL_TEST_TOKEN);
  }

  /**
   * @expectedException        TogglException
   * @expectedExceptionMessage Invalid parameters for getTimeEntries.
   */
  public function testTimeEntriesInvalidParameters() {
    $this->toggl->timeEntriesLoadRecent(0, NULL);
  }

  /**
   * @expectedException        TogglException
   * @expectedExceptionMessage Start date cannot be after the end date.
   */
  public function testTimeEntriesInvalidDates() {
    $this->toggl->timeEntriesLoadRecent(1, 0);
  }

  public function testWorkspaces() {
    $response = $this->toggl->workspaceLoadAll();
    $this->assertSame($response->code, 200);
    $this->assertSame(count($response->data->data), 2);
  }

  public function testClients() {
    $response = $this->toggl->clientLoadAll();
    $this->assertSame($response->code, 200);
    $this->assertSame(count($response->data->data), 3);
  }

  public function testUser() {
    $account = $this->toggl->userLoad();
    $this->assertSame($account->api_token, TOGGL_TEST_TOKEN);
    $this->assertSame($account->fullname, 'Toggl Tester');
    $this->assertSame($account->email, 'toggl-php-sdk@davereid.net');
    $this->assertSame($account->id, 202215);
  }
}
