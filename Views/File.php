<?php
/**
 * Aldu\Media\Views\File
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

class File extends Core\View
{
  protected static $configuration = array(
    __CLASS__ => array(
      'form' => array(
        'fields' => array(
          'data' => array(), 'name' => array(), 'locale' => array(), 'title' => array(), 'description' => array()
        )
      ),
      'table' => array(
        'columns' => array(
          'title' => 'Title',
          'name' => 'File name',
          'type' => 'File type',
          'size' => 'File size',
          'created' => 'Created'
        )
      )
    )
  );

  public function listview($models = array())
  {
    $ul = new Helper\HTML('ul.aldu-core-view-listview');
    $ul->data('role', 'listview');
    foreach ($models as $model) {
      $li = $ul->li();
      $a = $li->a(array(
        'href' => $model->url()
      ));
      $a->h3($model->title);
      $a->append('p.ui-li-aside', $model->created->format(ALDU_DATETIME_FORMAT));
    }
    return $ul;
  }

  public function read($file, $options = array())
  {
    extract(array_merge(array(
      'render' => $this->render
    ), $options));
    switch ($render) {
    case 'page':
    case 'dom':
    case 'embed':
      $embed = null;
      if (preg_match('/^http/', $file->path)) {
        $embed = new Helper\HTML('iframe', array(
          'src' => $file->path, 'frameborder' => '0'
        ));
        switch ($render) {
        case 'page':
          $page = new Helper\HTML\Page();
          $page->compose($embed);
          $embed = $page;
        default:
          return $this->response->body($embed);
        }
      }
      elseif (preg_match('/flash/', $file->type)) {
        $embed = new Helper\HTML('div');
        $object = $embed->object(array(
          'data' => $file->url(), 'type' => $file->type
        ));
        $object->param(array(
          'name' => 'movie', 'value' => $file->url()
        ));
        $object->param(array(
          'name' => 'wmode', 'value' => 'transparent'
        ));
      }
      else {
        $this->response->message($this->locale->t("Files of type %s cannot be rendered.", $file->type), LOG_ERR);
      }
      switch ($render) {
      case 'page':
        $page = new Helper\HTML\Page();
        $embed = $page->compose($embed);
      }
      return $this->response->body($embed);
    case 'download':
    case 'raw':
    default:
      $this->response->inline($file->name);
      if ($render == 'download') {
        $this->response->download($file->name);
      }
      $data = empty($file->data) ? file_get_contents(ALDU_UPLOAD . DS . $file->path) : $file->data;
      $this->response->header('Content-Length', strlen($data));
      $this->response->type($file->type);
      $this->response->cache($file->updated ? $file->updated->format('U') : $file->created->format('U'));
      return $this->response->body($data);
    }
  }
}
