<?php
/**
 * Aldu\Media\Models\Video
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
 * @package       Aldu\Media\Models
 * @uses          Aldu\Core
 * @since         AlduPHP(tm) v1.0.0
 * @license       Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 */

namespace Aldu\Media\Models;

use Aldu\Core;
use Aldu\Core\Utility\Shell;
use Exception;

class Video extends File
{
  const REGEX_DURATION = '/Duration: ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.([0-9]+))?/';
  const REGEX_FRAME_WH = '/Video:.+?([1-9][0-9]*)x([1-9][0-9]*)/';

  public $width;
  public $height;
  public $duration;
  public $crop;
  public $second;

  protected static $attributes = array(
    'width' => array(
      'type' => 'number'
    ), 'height' => array(
      'type' => 'number'
    ), 'duration' => array(
      'type' => 'number'
    ), 'second' => array(
      'type' => 'number'
    )
  );

  public function save()
  {
    if ($this->data) {
      if (!$ffmpeg = static::cfg('paths.ffmpeg') ? : Shell::exec('which ffmpeg')) {
        throw new Exception("ffmpeg not present. Specify ffmpeg path in %s config key [%s]", __CLASS__, 'paths.ffmpeg');
      }
      $filename = tempnam(sys_get_temp_dir(), __FUNCTION__);
      file_put_contents($filename, $this->data);
      $info = Shell::exec("$ffmpeg -i '$filename' 2>&1");
      preg_match(self::REGEX_FRAME_WH, $info, $dimensions);
      preg_match(self::REGEX_DURATION, $info, $duration);
      $this->width = (int) $dimensions[1];
      $this->height = (int) $dimensions[2];
      $this->duration = (int) (($duration[1] * 3600) + ($duration[2] * 60) + $duration[3]);
    }
    return parent::save();
  }
}
