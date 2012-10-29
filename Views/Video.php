<?php
/**
 * Aldu\Media\Views\Video
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
 * @package       Aldu\Media\Views
 * @uses          Aldu\Core
 * @since         AlduPHP(tm) v1.0.0
 * @license       Creative Commons Attribution-ShareAlike 3.0 Unported (CC BY-SA 3.0)
 */
namespace Aldu\Media\Views;

use Aldu\Core;
use Aldu\Core\Exception;
use Aldu\Core\View\Helper;
use Aldu\Core\Utility\ClassLoader;
use Aldu\Core\Utility\Shell;

class Video extends Image
{
  protected static $configuration = array(
    __CLASS__ => array(
      'form' => array(
        'fields' => array(
          'data' => array(
            'attributes' => array(
              'accept' => 'video/*'
            )
          ),
          'crop' => array(
            'title' => 'Thumbnail cropping'
          ),
          'second' => array(
            'title' => 'Thumbnail at second'
          )
        )
      )
    )
  );

  protected function createThumb($file, $options = array())
  {
    $options = array_merge(array(
      'width' => static::cfg('thumb.width'),
      'height' => static::cfg('thumb.height'),
      'crop' => $file->crop ? : static::cfg('thumb.crop'),
      'second' => $file->second
    ), array_filter($options));
    extract($options);
    if (!$ffmpeg = $this->model->cfg('paths.ffmpeg') ? : Shell::exec('which ffmpeg')) {
      throw new Exception("ffmpeg not present. Specify ffmpeg path in %s config key [%s]", __CLASS__, 'paths.ffmpeg');
    }
    $filepath = ALDU_UPLOAD . DS . $file->path;
    if (!$second || $second >= $file->duration) {
      $second = rand(1, ($file->duration - 1));
    }
    $cmd = "$ffmpeg -i '$filepath' -an -ss $second -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg - 2>/dev/null";
    $file->data = Shell::exec($cmd);
    return parent::createThumb($file, $options);
  }

  public function thumb($file, $options = array()) {
    $options = array_merge(array(
        'render' => $this->render,
        'width' => static::cfg('thumb.width'),
        'height' => static::cfg('thumb.height'),
        'crop' => $this->crop ? : static::cfg('thumb.crop'),
        'second' => $this->second
    ), $options);
    return parent::thumb($file, $options);
  }

  public function read($file, $options = array())
  {
    $options = array_merge(array(
      'render' => $this->render
    ), $options);
    extract($options);
    switch ($render) {
    case 'page':
      $page = new Helper\HTML\Page();
      $tag = new Helper\HTML('video',
        array(
          'preload' => 'auto', 'autoplay' => 'autoplay', 'controls' => 'controls', 'poster' => $file->url('thumb')
        ));
      $tag->append('source', array(
          'src' => $file->url('read'), 'type' => $file->type
        ));
      return $this->response->body($page->compose($tag));
    case 'dom':
    case 'embed':
      if (preg_match('/^http/', $file->path)) {
        $embed = new Helper\HTML('iframe', array(
          'src' => $file->path, 'frameborder' => '0'
        ));
      }
      else {
        $embed = new Helper\HTML('video',
          array(
            'preload' => 'auto', 'controls' => 'controls', 'poster' => $file->url('thumb')
          ));
        $embed->append('source', array(
            'src' => $file->url('view'), 'type' => $file->type
          ));
      }
      switch ($render) {
      case 'dom':
        return $embed;
      }
      return $this->response->body($embed);
    }
    return parent::read($file, $options);
  }
}
