<?php
/**
 * Aldu\Media\Controllers\File
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
use Aldu\Core\Utility\ClassLoader;

class File extends Core\Controller
{
  protected static $configuration = array(
    __CLASS__ => array(
      'factory' => array(
        'generic' => 'Aldu\Media\Models\File',
        '^image/' => 'Aldu\Media\Models\File\Image',
        '^video/' => 'Aldu\Media\Models\File\Video',
        '/pdf$' => 'Aldu\Media\Models\File\PDF'
      )
    )
  );

  public static function factory($type)
  {
  }

  protected function _read()
  {
    $args = func_get_args();
    $view = array_shift($args);
    $id = array_shift($args);
    if (!$model = $this->model->first($id)) {
      if ($model === false) {
        return $this->response->status(401);
      }
    }
    elseif (method_exists($this->view, $view)) {
      return call_user_func(array(
        $this->view,
        $view
      ), $model, array_shift($args));
    }
    return $this->response->status(404);
  }
}
