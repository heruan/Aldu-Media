<?php
/**
 * Aldu\Media\Views\Audio
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

class Audio extends File
{
  public static $configuration = array(
    __CLASS__ => array(
      'form' => array(
        'fields' => array(
          'data' => array(
            'attributes' => array(
              'accept' => 'audio/*'
            )
          )
        )
      )
    )
  );

  public function read($file, $options = array())
  {
    $options = array_merge(array(
      'render' => $this->render
    ), $options);
    extract($options);
    switch ($render) {
    case 'dom':
    case 'embed':
      $embed = new Helper\HTML('audio', array(
        'preload' => 'auto',
        'controls' => 'controls'
      ));
      $embed->append('source', array(
        'src' => $file->url('read'),
        'type' => $file->type
      ));
      if ($file->subtitles) {
        $embed->track(array(
          'kind' => 'descriptions',
          'label' => $file->subtitles->title,
          'src' => $file->subtitles->url('read')
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
