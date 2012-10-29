<?php
/**
 * Aldu\Media\Views\Image
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
use Aldu\Core\View\Helper;
use Aldu\Core\Utility\ClassLoader;
use Imagick;

class Image extends File
{
  protected static $configuration = array(
    __CLASS__ => array(
      'form' => array(
        'fields' => array(
          'data' => array(
            'attributes' => array(
              'accept' => 'image/*'
            )
          )
        )
      ),
      'thumb' => array(
        'format' => 'png',
        'width' => 80,
        'height' => 80,
        'crop' => 'center'
      )
    )
  );

  protected function createThumb($file, $options = array())
  {
    $options = array_merge(array(
      'width' => static::cfg('thumb.width'),
      'height' => static::cfg('thumb.height'),
      'crop' => $file->crop ? : static::cfg('thumb.crop'),
      'page' => 0
    ), array_filter($options));
    extract($options);
    if (empty($file->data) && file_exists(ALDU_UPLOAD . DS . $file->path)) {
      $img = new Imagick(ALDU_UPLOAD . DS . $file->path . "[$page]");
    }
    else {
      $img = new Imagick();
      $img->readImageBlob($file->data);
    }
    switch ($width) {
    case null:
      $width = static::cfg('thumb.width');
      break;
    case 'auto':
      $width = $img->getImageWidth();
      break;
    }
    switch ($height) {
    case null:
      $height = static::cfg('thumb.height');
      break;
    case 'auto':
      $height = $img->getImageHeight();
      break;
    }
    if (($img->getImageWidth() / $img->getImageHeight() > $width / $height)) {
      $img->resizeImage(0, $height, Imagick::FILTER_QUADRATIC, 1);
    }
    else {
      $img->resizeImage($width, 0, Imagick::FILTER_QUADRATIC, 1);
    }
    $left = ($img->getImageWidth() - $width) / 2;
    $top = ($img->getImageHeight() - $height) / 2;
    foreach (explode(' ', $crop) as $c => $crop) {
      switch ($crop) {
      case 'top':
        $top = 0;
        break;
      case 'left':
        $left = 0;
        break;
      case 'bottom':
        $top = $img->getImageHeight() - $height;
        break;
      case 'right':
        $left = $img->getImageWidth() - $width;
        break;
      default:
        if (is_numeric($crop)) {
          if ($c) {
            $top = ($img->getImageHeight() - $height) / 100 * $crop;
          }
          else {
            $left = ($img->getImageWidth() - $width) / 100 * $crop;
          }
        }
      }
      $img->cropImage($width, $height, $left, $top);
      $format = static::cfg('thumb.format');
      $img->setImageFormat($format);
      $file->type = 'image/' . $format;
      $file->data = $img->getImageBlob();
      return $file;
    }
  }

  public function thumb($file, $options = array())
  {
    $options = array_merge(array(
      'render' => $this->render,
      'width' => static::cfg('thumb.width'),
      'height' => static::cfg('thumb.height'),
      'crop' => $file->crop ? : static::cfg('thumb.crop')
    ), $options);
    extract($options);
    array_shift($options);
    switch ($render) {
    case 'dom':
    case 'embed':
      $embed = new Helper\HTML('img', array(
        'data-name' => $file->name,
        'src' => $file->url('thumb', array(
          'arguments' => array(
            $width,
            $height,
            $crop
          )
        )),
        'alt' => $file->title,
        'title' => $file->title
      ));
      switch ($render) {
      case 'dom':
        return $embed;
      case 'embed':
      default:
        return $this->response->body($embed);
      }
    }
    $lastmod = $file->updated ? $file->updated->format('U') : $file->created->format('U');
    $cache = ALDU_CACHE . DS . __FUNCTION__ . md5($file->id . $file->locale->id . $lastmod . implode('-', $options));
    if (file_exists($cache)) {
      $file->type = 'image/' . static::cfg('thumb.format');
      $file->data = file_get_contents($cache);
      return $this->read($file);
    }
    $file = $this->createThumb($file, $options);
    file_put_contents($cache, $file->data);
    return $this->read($file);
  }

  public function listview($models = array())
  {
    $ul = new Helper\HTML('ul.aldu-core-view-listview.aldu-media-views-image-listview');
    $ul->data('role', 'listview');
    foreach ($models as $model) {
      $li = $ul->li();
      $a = $li->a(array(
        'href' => $model->url()
      ));
      $a->append($this->thumb($model, array(
        'render' => 'dom'
      )));
      $a->h3($model->title);
      $a->append('p.ui-li-aside', $model->created->format(ALDU_DATETIME_FORMAT));
    }
    return $ul;
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
      $page->title($file->title);
      $tag = new Helper\HTML('img', array(
        'src' => $file->url('read'),
        'alt' => $file->title
      ));
      return $this->response->body($page->compose($tag));
    case 'dom':
    case 'embed':
      $embed = new Helper\HTML('img', array(
        'data-name' => $file->name ? : null,
        'src' => $file->url('read'),
        'alt' => $file->title,
        'title' => $file->title
      ));
      switch ($render) {
      case 'dom':
        return $embed;
      }
      return $this->response->body($embed);
    }
    return parent::read($file, $options);
  }
}
