<?php
/**
 * @file
 * Definition of Drupal\breakpoint\Tests\BreakpointsThemeTest.
 */

namespace Drupal\breakpoint\Tests;

use Drupal\breakpoint\Tests\BreakpointGroupTestBase;

/**
 * Test breakpoints provided by themes.
 */
class BreakpointThemeTest extends BreakpointGroupTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Breakpoint theme functionality',
      'description' => 'Thoroughly test the breakpoints provided by a theme.',
      'group' => 'Breakpoint',
    );
  }

  public function setUp() {
    parent::setUp();
    theme_enable(array('breakpoint_test_theme'));
  }

  /**
   * Test the breakpoint group created for a theme.
   */
  public function testThemeBreakpoints() {
    // Verify the breakpoint group for breakpoint_test_theme was created.
     $breakpoint_group = array(
      'label' => 'Breakpoint test theme',
      'id' => 'theme.breakpoint_test_theme.breakpointgroup',
      'breakpoints' => array(
        'theme.breakpoint_test_theme.mobile' => array(
          'id' => 'theme.breakpoint_test_theme.mobile',
          'name' => 'mobile',
          'label' => 'mobile',
          'mediaQuery' => '(min-width: 0px)',
          'source' => 'breakpoint_test_theme',
          'sourceType' => 'theme',
          'weight' => 0,
          'multipliers' => array(
            '1x' => '1x',
          ),
          'status' => TRUE,
          'langcode' => 'en',
        ),
        'theme.breakpoint_test_theme.narrow' => array(
          'id' => 'theme.breakpoint_test_theme.narrow',
          'name' => 'narrow',
          'label' => 'narrow',
          'mediaQuery' => '(min-width: 560px)',
          'source' => 'breakpoint_test_theme',
          'sourceType' => 'theme',
          'weight' => 1,
          'multipliers' => array(
            '1x' => '1x',
          ),
          'status' => TRUE,
          'langcode' => 'en',
        ),
        'theme.breakpoint_test_theme.wide' => array(
          'id' => 'theme.breakpoint_test_theme.wide',
          'name' => 'wide',
          'label' => 'wide',
          'mediaQuery' => '(min-width: 851px)',
          'source' => 'breakpoint_test_theme',
          'sourceType' => 'theme',
          'weight' => 2,
          'multipliers' => array(
            '1x' =>  '1x',
          ),
          'status' => TRUE,
          'langcode: en',
        ),
        'theme.breakpoint_test_theme.tv' => array(
          'id' => ' theme.breakpoint_test_theme.tv',
          'name' => 'tv',
          'label' => 'tv',
          'mediaQuery' => 'only screen and (min-width 3456px)',
          'source' => 'breakpoint_test_theme',
          'sourceType' => 'theme',
          'weight' => 3,
          'multipliers' => array(
            '1x' => '1x',
          ),
          'status' => TRUE,
          'langcode' => 'en',
        ),
      )
    );

    // Verify we can load this breakpoint defined by the theme.
    $this->verifyBreakpointGroup($breakpoint_group);
  }
}
