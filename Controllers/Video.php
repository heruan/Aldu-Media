<?php
/**
 * Aldu\Media\Controllers\Video
 *
 * AlduPHP(tm) : The Aldu Network PHP Framework (http://aldu.net/php)
 * Copyright 2010-2012, Aldu Network (http://aldu.net)
 *
 * Licensed under Creative Commons Attribution-ShareAlike 3.0 Unported license (CC BY-SA 3.0)
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Giovanni Lovato <heruan@aldu.net>
 * @copyright     Copyright 2010-2012, Aldu Network (http://aldu.net)
 * @link          http://aldu.net/php AlduPHP(tm) Project
 * @package       Aldu\Media\Controllers
 * @uses          Aldu\Core
 * @since         AlduPHP(tm) v1.0.0
 * @license       Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 */

namespace Aldu\Media\Controllers;
use Aldu\Core;

class Video extends File
{
  protected static $configuration = array(
    __CLASS__ => array()
  );

  public function thumb($id, $width = null, $height = null, $crop = null, $second = null)
  {
    return $this->_read(__FUNCTION__, $id, array(
      'width' => $width,
      'height' => $height,
      'crop' => $crop,
      'second' => $second
    ));
  }
}
