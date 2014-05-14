<?php

/**
 * @file
 * Definition of Drupal\responsive_image\Tests\ResponsiveImageAdminUITest.
 */

namespace Drupal\responsive_image\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\breakpoint\Entity\Breakpoint;

/**
 * Tests for breakpoint sets admin interface.
 */
class ResponsiveImageAdminUITest extends WebTestBase {

  /**
   * The breakpoint group for testing.
   *
   * @var \Drupal\breakpoint\Entity\BreakpointGroupInterface
   */
  protected $breakpointGroup;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('responsive_image');

  /**
   * Drupal\simpletest\WebTestBase\getInfo().
   */
  public static function getInfo() {
    return array(
      'name' => 'Responsive Image administration functionality',
      'description' => 'Thoroughly test the administrative interface of the Responsive Image module.',
      'group' => 'Responsive Image',
    );
  }

  /**
   * Drupal\simpletest\WebTestBase\setUp().
   */
  public function setUp() {
    parent::setUp();

    // Create user.
    $this->admin_user = $this->drupalCreateUser(array(
      'administer responsive images',
    ));

    $this->drupalLogin($this->admin_user);

    // Add breakpoint_group and breakpoints.
    $this->breakpointGroup = entity_create('breakpoint_group', array(
      'name' => 'atestset',
      'label' => 'A test set',
      'sourceType' => Breakpoint::SOURCE_TYPE_USER_DEFINED,
    ));

    $breakpoint_names = array('small', 'medium', 'large');
    for ($i = 0; $i < 3; $i++) {
      $width = ($i + 1) * 200;
      $breakpoint = entity_create('breakpoint', array(
        'name' => $breakpoint_names[$i],
        'mediaQuery' => "(min-width: {$width}px)",
        'source' => 'user',
        'sourceType' => Breakpoint::SOURCE_TYPE_USER_DEFINED,
        'multipliers' => array(
          '1.5x' => 0,
          '2x' => '2x',
        ),
      ));
      $breakpoint->save();
      $this->breakpointGroup->addBreakpoints(array($breakpoint));
    }
    $this->breakpointGroup->save();

  }

  /**
   * Test responsive image administration functionality.
   */
  public function testResponsiveImageAdmin() {
    // We start without any default mappings.
    $this->drupalGet('admin/config/media/responsive-image-mapping');
    $this->assertText('There is no Responsive image mapping yet.');

    // Add a new responsive image mapping, our breakpoint set should be selected.
    $this->drupalGet('admin/config/media/responsive-image-mapping/add');
    $this->assertFieldByName('breakpointGroup', $this->breakpointGroup->id());

    // Create a new group.
    $edit = array(
      'label' => 'Mapping One',
      'id' => 'mapping_one',
      'breakpointGroup' => $this->breakpointGroup->id(),
    );
    $this->drupalPostForm('admin/config/media/responsive-image-mapping/add', $edit, t('Save'));

    // Check if the new group is created.
    $this->assertResponse(200);
    $this->drupalGet('admin/config/media/responsive-image-mapping');
    $this->assertNoText('There is no Responsive image mapping yet.');
    $this->assertText('Mapping One');
    $this->assertText('mapping_one');

    // Edit the group.
    $this->drupalGet('admin/config/media/responsive-image-mapping/mapping_one');
    $this->assertFieldByName('label', 'Mapping One');
    $this->assertFieldByName('breakpointGroup', $this->breakpointGroup->id());

    // Check if the radio buttons are present.
    $this->assertFieldByName('mappings[custom.user.small][1x][mapping_type]', '');
    $this->assertFieldByName('mappings[custom.user.small][2x][mapping_type]', '');
    $this->assertFieldByName('mappings[custom.user.medium][1x][mapping_type]', '');
    $this->assertFieldByName('mappings[custom.user.medium][2x][mapping_type]', '');
    $this->assertFieldByName('mappings[custom.user.large][1x][mapping_type]', '');
    $this->assertFieldByName('mappings[custom.user.large][2x][mapping_type]', '');

    // Check if the image style dropdowns are present.
    $this->assertFieldByName('mappings[custom.user.small][1x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.small][2x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.medium][1x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.medium][2x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.large][1x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.large][2x][image_style]', '');

    // Check if the sizes textfields are present.
    $this->assertFieldByName('mappings[custom.user.small][1x][sizes]', '');
    $this->assertFieldByName('mappings[custom.user.small][2x][sizes]', '');
    $this->assertFieldByName('mappings[custom.user.medium][1x][sizes]', '');
    $this->assertFieldByName('mappings[custom.user.medium][2x][sizes]', '');
    $this->assertFieldByName('mappings[custom.user.large][1x][sizes]', '');
    $this->assertFieldByName('mappings[custom.user.large][2x][sizes]', '');

    // Check if the image styles checkboxes are present.
    foreach (array_keys(image_style_options(FALSE)) as $image_style_name) {
      $this->assertFieldByName('mappings[custom.user.small][1x][sizes_image_styles][' . $image_style_name . ']');
      $this->assertFieldByName('mappings[custom.user.small][2x][sizes_image_styles][' . $image_style_name . ']');
      $this->assertFieldByName('mappings[custom.user.medium][1x][sizes_image_styles][' . $image_style_name . ']');
      $this->assertFieldByName('mappings[custom.user.medium][2x][sizes_image_styles][' . $image_style_name . ']');
      $this->assertFieldByName('mappings[custom.user.large][1x][sizes_image_styles][' . $image_style_name . ']');
      $this->assertFieldByName('mappings[custom.user.large][2x][sizes_image_styles][' . $image_style_name . ']');
    }

    // Save mappings for 1x variant only.
    $edit = array(
      'label' => 'Mapping One',
      'breakpointGroup' => $this->breakpointGroup->id(),
      'mappings[custom.user.small][1x][mapping_type]' => 'image_style',
      'mappings[custom.user.small][1x][image_style]' => 'thumbnail',
      'mappings[custom.user.medium][1x][mapping_type]' => 'sizes',
      'mappings[custom.user.medium][1x][sizes]' => '(min-width: 700px) 700px, 100vw',
      'mappings[custom.user.medium][1x][sizes_image_styles][large]' => 'large',
      'mappings[custom.user.medium][1x][sizes_image_styles][medium]' => 'medium',
      'mappings[custom.user.large][1x][mapping_type]' => 'image_style',
      'mappings[custom.user.large][1x][image_style]' => 'large',
    );
    $this->drupalPostForm('admin/config/media/responsive-image-mapping/mapping_one', $edit, t('Save'));
    $this->drupalGet('admin/config/media/responsive-image-mapping/mapping_one');
    $this->assertFieldByName('mappings[custom.user.small][1x][image_style]', 'thumbnail');
    $this->assertFieldByName('mappings[custom.user.small][1x][mapping_type]', 'image_style');
    $this->assertFieldByName('mappings[custom.user.small][2x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.small][2x][mapping_type]', '_none');
    $this->assertFieldByName('mappings[custom.user.medium][1x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.medium][1x][mapping_type]', 'sizes');
    $this->assertFieldByName('mappings[custom.user.medium][1x][sizes]', '(min-width: 700px) 700px, 100vw');
    $this->assertFieldChecked('edit-mappings-customusermedium-1x-sizes-image-styles-large');
    $this->assertFieldChecked('edit-mappings-customusermedium-1x-sizes-image-styles-medium');
    $this->assertNoFieldChecked('edit-mappings-customusermedium-1x-sizes-image-styles-thumbnail');
    $this->assertFieldByName('mappings[custom.user.medium][2x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.medium][2x][mapping_type]', '_none');
    $this->assertFieldByName('mappings[custom.user.large][1x][image_style]', 'large');
    $this->assertFieldByName('mappings[custom.user.large][1x][mapping_type]', 'image_style');
    $this->assertFieldByName('mappings[custom.user.large][2x][image_style]', '');
    $this->assertFieldByName('mappings[custom.user.large][2x][mapping_type]', '_none');

    // Delete the mapping.
    $this->drupalGet('admin/config/media/responsive-image-mapping/mapping_one/delete');
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->drupalGet('admin/config/media/responsive-image-mapping');
    $this->assertText('There is no Responsive image mapping yet.');
  }

}
