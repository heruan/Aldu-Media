<?php
/**
 * Aldu\Media\Models\File
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
use finfo;

class File extends Core\Locale\Localized
{
  public $name;
  public $type;
  public $size;
  public $title;
  public $description;
  public $path;
  public $data;

  protected static $attributes = array(
    'size' => array(
      'type' => 'number'
    ),
    'data' => array(
      'type' => 'file'
    ),
    'description' => array(
      'type' => 'textarea'
    )
  );

  protected static $extensions = array(
    'localized' => array(
      'attributes' => array(
        'title' => true,
        'description' => true,
        'size' => true,
        'path' => true,
        'data' => true
      )
    )
  );

  public function save()
  {
    if (!$this->path) {
      $this->path = uniqid($this->name());
    }
    if ($this->data) {
      $data = $this->data;
      $this->data = null;
      $path = ALDU_UPLOAD . DS . $this->path;
      file_put_contents($path, $data);
      $this->size = filesize($path);
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $this->type = $finfo->file($path);
    }
    return parent::save();
  }

  public function delete()
  {
    if ($delete = parent::delete()) {
      if ($this->path) {
        $path = ALDU_UPLOAD . DS . $this->path;
        if (file_exists($path)) {
          unlink($path);
        }
      }
    }
    return $delete;
  }
}
