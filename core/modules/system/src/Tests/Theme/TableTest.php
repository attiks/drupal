<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Theme\TableTest.
 */

namespace Drupal\system\Tests\Theme;

use Drupal\Component\Utility\String;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Unit tests for theme_table().
 */
class TableTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system');

  public static function getInfo() {
    return array(
      'name' => 'Theme Table',
      'description' => 'Tests built-in table theme functions.',
      'group' => 'Theme',
    );
  }

  /**
   * Tableheader.js provides 'sticky' table headers, and is included by default.
   */
  function testThemeTableStickyHeaders() {
    $header = array('one', 'two', 'three');
    $rows = array(array(1,2,3), array(4,5,6), array(7,8,9));
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#sticky' => TRUE,
    );
    $this->render($table);
    $js = _drupal_add_js();
    $this->assertTrue(isset($js['core/misc/tableheader.js']), 'tableheader.js found.');
    $this->assertRaw('sticky-enabled');
    drupal_static_reset('_drupal_add_js');
  }

  /**
   * If $sticky is FALSE, no tableheader.js should be included.
   */
  function testThemeTableNoStickyHeaders() {
    $header = array('one', 'two', 'three');
    $rows = array(array(1,2,3), array(4,5,6), array(7,8,9));
    $attributes = array();
    $caption = NULL;
    $colgroups = array();
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => $attributes,
      '#caption' => $caption,
      '#colgroups' => $colgroups,
      '#sticky' => FALSE,
    );
    $this->render($table);
    $js = _drupal_add_js();
    $this->assertFalse(isset($js['core/misc/tableheader.js']), 'tableheader.js not found.');
    $this->assertNoRaw('sticky-enabled');
    drupal_static_reset('_drupal_add_js');
  }

  /**
   * Tests that the table header is printed correctly even if there are no rows,
   * and that the empty text is displayed correctly.
   */
  function testThemeTableWithEmptyMessage() {
    $header = array(
      'Header 1',
      array(
        'data' => 'Header 2',
        'colspan' => 2,
      ),
    );
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => array(),
      '#empty' => 'Empty row.',
    );
    $this->render($table);
    $this->removeWhiteSpace();
    $this->assertRaw('<thead><tr><th>Header 1</th><th colspan="2">Header 2</th></tr>', 'Table header found.');
    $this->assertRaw('<tr class="odd"><td colspan="3" class="empty message">Empty row.</td>', 'Colspan on #empty row found.');
  }

  /**
   * Tests that the 'no_striping' option works correctly.
   */
  function testThemeTableWithNoStriping() {
    $rows = array(
      array(
        'data' => array(1),
        'no_striping' => TRUE,
      ),
    );
    $table = array(
      '#type' => 'table',
      '#rows' => $rows,
    );
    $this->render($table);
    $this->assertNoRaw('class="odd"', 'Odd/even classes were not added because $no_striping = TRUE.');
    $this->assertNoRaw('no_striping', 'No invalid no_striping HTML attribute was printed.');
  }

  /**
   * Tests that the 'header' option in cells works correctly.
   */
  function testThemeTableHeaderCellOption() {
    $rows = array(
      array(
        array('data' => 1, 'header' => TRUE),
        array('data' => 1, 'header' => FALSE),
        array('data' => 1),
      ),
    );
    $table = array(
      '#type' => 'table',
      '#rows' => $rows,
    );
    $this->render($table);
    $this->removeWhiteSpace();
    $this->assertRaw('<th>1</th><td>1</td><td>1</td>', 'The th and td tags was printed correctly.');
  }

  /**
   * Tests that the 'responsive-table' class is applied correctly.
   */
  public function testThemeTableResponsive() {
    $header = array('one', 'two', 'three');
    $rows = array(array(1,2,3), array(4,5,6), array(7,8,9));
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#responsive' => TRUE,
    );
    $this->render($table);
    $this->assertRaw('responsive-enabled', 'The responsive-enabled class was printed correctly.');
  }

  /**
   * Tests that the 'responsive-table' class is not applied without headers.
   */
  public function testThemeTableNotResponsiveHeaders() {
    $rows = array(array(1,2,3), array(4,5,6), array(7,8,9));
    $table = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#responsive' => TRUE,
    );
    $this->render($table);
    $this->assertNoRaw('responsive-enabled', 'The responsive-enabled class is not applied without table headers.');
  }

  /**
   * Tests that 'responsive-table' class only applied when responsive is TRUE.
   */
  public function testThemeTableNotResponsiveProperty() {
    $header = array('one', 'two', 'three');
    $rows = array(array(1,2,3), array(4,5,6), array(7,8,9));
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#responsive' => FALSE,
    );
    $this->render($table);
    $this->assertNoRaw('responsive-enabled', 'The responsive-enabled class is not applied without the "responsive" property set to TRUE.');
  }

  /**
   * Tests 'priority-medium' and 'priority-low' classes.
   */
  public function testThemeTableResponsivePriority() {
    $header = array(
      // Test associative header indices.
      'associative_key' => array('data' => 1, 'class' => array(RESPONSIVE_PRIORITY_MEDIUM)),
      // Test non-associative header indices.
      array('data' => 2, 'class' => array(RESPONSIVE_PRIORITY_LOW)),
      // Test no responsive priorities.
      array('data' => 3),
    );
    $rows = array(array(4, 5, 6));
    $table = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#responsive' => TRUE,
    );
    $this->render($table);
    $this->assertRaw('<th class="priority-medium">1</th>', 'Header 1: the priority-medium class was applied correctly.');
    $this->assertRaw('<th class="priority-low">2</th>', 'Header 2: the priority-low class was applied correctly.');
    $this->assertRaw('<th>3</th>', 'Header 3: no priority classes were applied.');
    $this->assertRaw('<td class="priority-medium">4</td>', 'Cell 1: the priority-medium class was applied correctly.');
    $this->assertRaw('<td class="priority-low">5</td>', 'Cell 2: the priority-low class was applied correctly.');
    $this->assertRaw('<td>6</td>', 'Cell 3: no priority classes were applied.');
  }

  /**
   * Renders a given render array.
   *
   * @param array $elements
   *   The render array elements to render.
   *
   * @return string
   *   The rendered HTML.
   */
  protected function render(array $elements) {
    $this->content = drupal_render($elements);
    $this->verbose('<pre>' . String::checkPlain($this->content));
  }

  /**
   * Removes all white-space between HTML tags from $this->content.
   */
  protected function removeWhiteSpace() {
    $this->content = preg_replace('@>\s+<@', '><', $this->content);
  }

  /**
   * Asserts that a raw string appears in $this->content.
   *
   * @param string $value
   *   The expected string.
   * @param string $message
   *   (optional) A custom assertion message.
   */
  protected function assertRaw($value, $message = NULL) {
    if (!isset($message)) {
      $message = String::format("Raw value @value found.", array(
        '@value' => var_export($value, TRUE),
      ));
    }
    $this->assert(strpos($this->content, $value) !== FALSE, $message);
  }

  /**
   * Asserts that a raw string does not appear in $this->content.
   *
   * @param string $value
   *   The not expected string.
   * @param string $message
   *   (optional) A custom assertion message.
   */
  protected function assertNoRaw($value, $message = NULL) {
    if (!isset($message)) {
      $message = String::format("Raw value @value not found.", array(
        '@value' => var_export($value, TRUE),
      ));
    }
    $this->assert(strpos($this->content, $value) === FALSE, $message);
  }

}
