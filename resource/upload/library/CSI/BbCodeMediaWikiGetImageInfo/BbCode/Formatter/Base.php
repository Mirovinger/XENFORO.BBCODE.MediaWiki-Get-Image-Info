<?php namespace CSI\BbCodeMediaWikiGetImageInfo\BbCode\Formatter;

/**
 * Class Base
 * @package CSI\BbCodeMediaWikiGetImageInfo\BbCode\Formatter
 */
class Base
{
  /**
   * @param array $tag
   * @param array $rendererStates
   * @param \XenForo_BbCode_Formatter_Base $formatter
   * @return mixed
   */
  public static function getBbCodeMediaWikiGetImageInfo(array $tag, array $rendererStates, \XenForo_BbCode_Formatter_Base $formatter)
  {
    $xenOptions = \XenForo_Application::get('options');
    $xenVisitor = \XenForo_Visitor::getInstance();
    $tagOption = array_map('trim', explode('|', $tag['option']));

    if (count($tagOption) > 1) {
      $optDefault = $tagOption[0];
    } else {
      $optDefault = $tag['option'];
    }

    $tagContent = $formatter->renderSubTree($tag['children'], $rendererStates);

    if (!$xenOptions->csiXF_bbCode_E04235F2_onoff || !$xenOptions->csiXF_bbCode_E04235F2_urlApi) {
      return $formatter->renderInvalidTag($tag, $rendererStates);
    }

    $tagContent = rawurlencode($tagContent);

    $getData = $xenOptions->csiXF_bbCode_E04235F2_urlApi . '?action=query&prop=imageinfo&iiprop=' . $xenOptions->csiXF_bbCode_E04235F2_iiprop . '&iiurlwidth=' . $xenOptions->csiXF_bbCode_E04235F2_iiurlwidth . '&titles=File:' . $tagContent . '&format=json';
    $decodeData = json_decode(file_get_contents($getData), true);
    $queryData = $decodeData['query']['pages'];

    foreach ($queryData as $page) {
      $tagTitle = $page['title'];

      if (!isset($page['imageinfo'])) {
        return $formatter->renderInvalidTag($tag, $rendererStates);
      }

      foreach ($page['imageinfo'] as $imageInfo) {
        $tagUser = $imageInfo['user'];
        $tagUrl = $imageInfo['url'];
        $tagSize = $imageInfo['size'];
        $tagTimestamp = $imageInfo['timestamp'];
        $tagThumbUrl = $imageInfo['thumburl'];
        $tagThumbWidth = $imageInfo['thumbwidth'];
        $tagThumbHeight = $imageInfo['thumbheight'];
      }
    }

    if (isset($tagSize)) {
      if ($tagSize >= 1073741824) {
        $tagSize = number_format($tagSize / 1073741824, 2) . ' GB';
      } elseif ($tagSize >= 1048576) {
        $tagSize = number_format($tagSize / 1048576, 2) . ' MB';
      } elseif ($tagSize >= 1024) {
        $tagSize = number_format($tagSize / 1024, 2) . ' KB';
      } elseif ($tagSize >= 1) {
        $tagSize = $tagSize . ' B';
      } else {
        $tagSize = '0 B';
      }
    }

    if (isset($tagTimestamp)) {
      $tagTimestamp = date('Y-m-d H:i:s', strtotime($tagTimestamp));
    }

    $view = $formatter->getView();

    if ($view) {
      $template = $view->createTemplateObject('csiXF_bbCode_E04235F2_tag_wiki_img',
        array(
          'title' => $tagTitle,
          'user' => $tagUser,
          'url' => $tagUrl,
          'size' => $tagSize,
          'timestamp' => $tagTimestamp,
          'thumburl' => $tagThumbUrl,
          'thumbwidth' => $tagThumbWidth,
          'thumbheight' => $tagThumbHeight,
        ));

      $tagContent = $template->render();
      return trim($tagContent);
    }

    return $tagContent;
  }
}
