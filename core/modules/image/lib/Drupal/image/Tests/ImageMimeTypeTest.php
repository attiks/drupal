<?php

/**
 * @file
 * Contains \Drupal\responsive_image\Tests\ResponsiveImageMappingEntityTest.
 */

namespace Drupal\image\Tests;

use Drupal\Tests\UnitTestCase;
use \Drupal\image\Entity\ImageStyle;

/**
 * @coversDefaultClass \Drupal\image\Entity\ImageStyle
 *
 * @group Drupal
 * @group Image
 */
class ImageStyleTest extends UnitTestCase {

  /**
   * The image effect used for testing.
   *
   * @var \Drupal\image\ImageEffectInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $imageEffect;

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The ID of the type of the entity under test.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\image\Entity\ImageStyle unit test',
      'group' => 'Image',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityTypeId = $this->randomName();
    $this->provider = $this->randomName();
    $this->entityType = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->entityType->expects($this->any())
                     ->method('getProvider')
                     ->will($this->returnValue($this->provider));
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->any())
                        ->method('getDefinition')
                        ->with($this->entityTypeId)
                        ->will($this->returnValue($this->entityType));
    $this->imageEffectId = $this->randomName();
    $this->imageEffect = $this->getMockBuilder('\Drupal\image\ImageEffectBase')
        ->setConstructorArgs(array(array(), $this->imageEffectId, array()))
        ->getMock();
    $this->imageEffect->expects($this->any())
        ->method('transformMimeType')
        ->will($this->returnCallback(function (&$mime_type) { $mime_type = 'image/webp';}));
    $this->effectManager = $this->getMock('\Drupal\Component\Plugin\PluginManagerInterface');

    $container = new ContainerBuilder();
    $container->set('plugin.manager.image.effect', $this->effectManager);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::transformMimeType
   */
  public function testTransformMimeType() {
    $this->effectManager->expects($this->any())
        ->method('getDefinition')
        ->with($this->imageEffectId)
        ->will($this->returnValue($this->imageEffect));
    $image_style = new ImageStyle(array('effects' => array($this->imageEffectId => array())), $this->entityTypeId);
    $mime_type = 'image/jpeg';
    $image_style->transformMimeType($mime_type);
    $this->assertEquals($mime_type, 'image/webp');
  }

}
