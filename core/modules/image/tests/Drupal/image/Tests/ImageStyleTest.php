<?php

/**
 * @file
 * Contains \Drupal\image\Tests\ImageStyleTest.
 */

namespace Drupal\image\Tests;

use Drupal\Core\DependencyInjection\ContainerBuilder;
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
   * The effect manager used for testing.
   *
   * @var \Drupal\image\ImageEffectManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $effectManager;

  /**
   * The image effect used for testing.
   *
   * @var \Drupal\image\ImageEffectInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $imageEffect;

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
    $this->effectManager = $this->getMockBuilder('\Drupal\image\ImageEffectManager')
        ->disableOriginalConstructor()
        ->getMock();
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::transformMimeType
   */
  public function testTransformMimeType() {
    $image_effect_id = $this->randomName();
    $image_effect = $this->getMockBuilder('\Drupal\image\ImageEffectBase')
        ->setConstructorArgs(array(array(), $image_effect_id, array()))
        ->getMock();
    $image_effect->expects($this->any())
        ->method('transformMimeType')
        ->will($this->returnCallback(function (&$mime_type) { $mime_type = 'image/webp';}));
    $this->effectManager->expects($this->any())
        ->method('get')
        ->with($image_effect_id)
        ->will($this->returnValue($image_effect));
    $this->effectManager->expects($this->any())
        ->method('createInstance')
        ->with($image_effect_id)
        ->will($this->returnValue($image_effect));
    \Drupal::getContainer()->set('plugin.manager.image.effect', $this->effectManager);

    $image_style = new ImageStyle(array('effects' => array($image_effect_id => array('id' => $image_effect_id))), $this->entityTypeId);
    $mime_types = array('image/jpeg', 'image/gif', 'image/png');
    foreach ($mime_types as $mime_type) {
      $image_style->transformMimeType($mime_type);
      $this->assertEquals($mime_type, 'image/webp');
    }
  }
}
