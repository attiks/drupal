<?php

/**
 * @file
 * Contains \Drupal\comment\CommentStorageInterface.
 */

namespace Drupal\comment;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines a common interface for comment entity controller classes.
 */
interface CommentStorageInterface extends EntityStorageInterface {

  /**
   * Gets the maximum encoded thread value for the top level comments.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   A comment entity.
   *
   * @return string
   *   The maximum encoded thread value among the top level comments of the
   *   node $comment belongs to.
   */
  public function getMaxThread(CommentInterface $comment);

  /**
   * Gets the maximum encoded thread value for the children of this comment.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   A comment entity.
   *
   * @return string
   *   The maximum encoded thread value among all replies of $comment.
   */
  public function getMaxThreadPerThread(CommentInterface $comment);

  /**
   * Calculates the page number for the first new comment.
   *
   * @param int $total_comments
   *   The total number of comments that the entity has.
   * @param int $new_comments
   *   The number of new comments that the entity has.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to which the comments belong.
   * @param string $field_name
   *   The field name on the entity to which comments are attached.
   *
   * @return array|null
   *   The page number where first new comment appears. (First page returns 0.)
   */
  public function getNewCommentPageNumber($total_comments, $new_comments, ContentEntityInterface $entity, $field_name = 'comment');

  /**
   * Gets the display ordinal or page number for a comment.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   The comment to use as a reference point.
   * @param int $comment_mode
   *   Comment mode (CommentManagerInterface::COMMENT_MODE_FLAT or
   *   CommentManagerInterface::COMMENT_MODE_THREADED).
   * @param int $divisor
   *   Defaults to 1, which returns the display ordinal for a comment. If the
   *   number of comments per page is provided, the returned value will be the
   *   page number. (The return value will be divided by $divisor.)
   *
   * @return int
   *   The display ordinal or page number for the comment. It is 0-based, so
   *   will represent the number of items before the given comment/page.
   */
  public function getDisplayOrdinal(CommentInterface $comment, $comment_mode, $divisor = 1);

  /**
   * Gets the comment ids of the passed comment entities' children.
   *
   * @param array $comments
   *   An array of comment entities keyed by their ids.
   * @return array
   *   The entity ids of the passed comment entities' children as an array.
   */
  public function getChildCids(array $comments);

  /**
   * Updates the comment statistics for a given node.
   *
   * The {comment_entity_statistics} table has the following fields:
   * - last_comment_timestamp: The timestamp of the last comment for the entity,
   *   or the entity created timestamp if no comments exist for the entity.
   * - last_comment_name: The name of the anonymous poster for the last comment.
   * - last_comment_uid: The user ID of the poster for the last comment for
   *   this entity, or the entity author's user ID if no comments exist for the
   *   entity.
   * - comment_count: The total number of approved/published comments on this
   *   entity.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   The comment being saved.
   */
  public function updateEntityStatistics(CommentInterface $comment);

  /**
   * Returns the number of unapproved comments.
   *
   * @return int
   *   The number of unapproved comments.
   */
  public function getUnapprovedCount();

}
