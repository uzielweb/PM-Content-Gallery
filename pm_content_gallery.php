<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pm_content_gallery
 *
 * @copyright
 * @license     GNU/Public
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class PlgContentPm_content_gallery extends JPlugin
{
    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {
        if (version_compare(JVERSION, '4.0', '>=')) {
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wa->registerAndUseStyle('pm_content_gallery_default', Uri::root() . 'plugins/content/pm_content_gallery/assets/css/owl.theme.default.min.css', ['version' => 'auto', 'relative' => true]);
            $wa->registerAndUseStyle('pm_content_gallery_carousel', Uri::root() . 'plugins/content/pm_content_gallery/assets/css/owl.carousel.min.css', ['version' => 'auto', 'relative' => true]);
            $wa->registerAndUseStyle('pm_content_gallery_temabasico', Uri::root() . 'plugins/content/pm_content_gallery/assets/css/temabasico.css', ['version' => 'auto', 'relative' => true]);
            $wa->registerAndUseScript('pm_content_gallery_js', Uri::root() . 'plugins/content/pm_content_gallery/assets/js/owl.carousel.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
        } else {
            $doc = Factory::getDocument();
            $doc->addStyleSheet(Uri::root() . 'plugins/content/pm_content_gallery/assets/css/owl.theme.default.min.css');
            $doc->addStyleSheet(Uri::root() . 'plugins/content/pm_content_gallery/assets/css/owl.carousel.min.css');
            $doc->addStyleSheet(Uri::root() . 'plugins/content/pm_content_gallery/assets/css/temabasico.css');
            JHtml::_('jquery.framework', true, true);
            $doc->addScript(Uri::root() . 'plugins/content/pm_content_gallery/assets/js/owl.carousel.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
        }

        preg_match_all('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $article->text, $matches);
        $allMatches = $matches[0];
        $html[] = "";
        $modalsHtml = ""; // String to store modals HTML separately

        foreach ($allMatches as $m=>$match) {
            preg_match_all('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $match, $newmatches);
            $newMatchesContent[$m] = $newmatches[1][0];
            $contentArray = explode("|", isset($newMatchesContent[$m]) ? $newMatchesContent[$m] : '');
            $pasta = isset($contentArray[0]) ? $contentArray[0] : '';
            $descricao = isset($contentArray[1]) ? $contentArray[1] : '';

            $html[$m] = '';
            $html[$m] .= '<section class="pmcontentgallery gallery-'.$article->id.$m.'">';
            $html[$m] .= '<div class="owl-carousel carrossel-'.$article->id.$m.' owl-theme">';
            $directory = $this->params->get('folder', 'images') . '/' . $pasta;
            if (is_dir($directory)) {
                $files = preg_grep('~\.(jpeg|jpg|png|gif|JPEG|JPG|PNG|GIF)$~', scandir($directory));
            } else {
                echo "The directory doesn't exist: $directory";
            }

            foreach ($files as $k => $file) {
                $imagem = Uri::base() . $directory . '/' . $file;
                $heightb4 = $this->params->get("height", "16by9");
                $heightb5 = str_replace("by", "x", $heightb4);
                $imageName = pathinfo($file, PATHINFO_FILENAME);
                $imageName = str_replace(["-", "_"], " ", $imageName);
                $imageName = ucwords($imageName);
                $alt = $descricao ? $descricao . ' - ' . $k : $imageName;
                // check image is portrait or landscape
                $image = getimagesize($directory . '/' . $file);
                $imageWidth = $image[0];
                $imageHeight = $image[1];
                $imageRatio = $imageWidth / $imageHeight;
                $imageOrientation = $imageRatio > 1 ? 'landscape' : 'portrait';
                $html[$m] .= '<div class="item">';
                $html[$m] .= '<div class="embed-responsive embed-responsive-' . $heightb4 . ' ratio ratio-' . $heightb5 . ' ' . $imageOrientation . '">';
                if ($this->params->get("modal", "1") == "1") {
                    $html[$m] .= '<a href="#" data-toggle="modal" data-target="#galleryModal-' . $article->id . '-' . $m . '-' . $k . '" rel="gallery-' . $article->id . '-' . $m . '" data-bs-toggle="modal" data-bs-target="#galleryModal-' . $article->id . '-' . $m . '-' . $k . '" rel="gallery-' . $article->id . '-' . $m . '" gallery="' . $article->id . '-' . $m . '">';
                }
                $html[$m] .= '<img class="embed-responsive-item w-100 h-auto" src="' . $imagem . '" alt="' . $alt . '">';
                if ($this->params->get("modal", "1") == "1") {
                    $html[$m] .= '</a>';
                }
                $html[$m] .= '</div>';
                $html[$m] .= '</div>';

              
            }
            $html[$m] .= '</div>';
            $html[$m] .= '<div class="description">' . $descricao . '</div>';
            $html[$m] .= '</section>';
            $html[$m] .= '<script>
                jQuery(document).ready(function($){
                    $(".carrossel-'.$article->id.$m.'").owlCarousel({
                        loop:' . $this->params->get("loop", "true") . ',
                        autoplay: ' . $this->params->get("autoplay", "true") . ',
                        margin:' . $this->params->get("margin", "10") . ',
                        nav:' . $this->params->get("nav", "true") . ',
                        dots:' . $this->params->get("dots", "true") . ',
                        dotsEach: ' . $this->params->get("dotseach", "1") . ',
                        lazyLoad: ' . $this->params->get("lazyload", "true") . ',
                        responsive:{
                            0:{
                                items: 1
                            },
                            767:{
                                items: ' . round($this->params->get("images_per_row") / 2) . '
                            },
                            1000:{
                                items:' . $this->params->get("images_per_row") . '
                            }
                        }
                    });
                });
            </script>';

            // Append modals HTML to the end of article text

            $article->text = preg_replace('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $html[$m], $article->text, 1);
        }
        if ($this->params->get("modal", "1") == "1") {
            $modalsHtml = '<div class="pm-modal-gallery pm-modal-gallery-' . $article->id . ' position-relative">';
        foreach ($files as $k => $file) {
            $imagem = Uri::base() . $directory . '/' . $file;
            $heightb4 = $this->params->get("height", "16by9");
            $heightb5 = str_replace("by", "x", $heightb4);
            $imageName = pathinfo($file, PATHINFO_FILENAME);
            $imageName = str_replace(["-", "_"], " ", $imageName);
            $imageName = ucwords($imageName);
            $alt = $descricao ? $descricao . ' - ' . $k : $imageName;
           
                $modalsHtml .= '<div class="modal fade" id="galleryModal-' . $article->id . '-' . $m . '-' . $k . '" tabindex="-1" aria-labelledby="galleryModalLabel-' . $article->id . '-' . $m . '-' . $k . '" aria-hidden="true">';
                $modalsHtml .= '<div class="modal-dialog modal-lg" role="document">';
                $modalsHtml .= '<div class="modal-content">';
                $modalsHtml .= '<div class="modal-body">';
                $modalsHtml .= '<img class="w-100" src="' . $imagem . '" alt="' . $alt . '">';
                $modalsHtml .= '</div>';
                $modalsHtml .= '</div>';
                $modalsHtml .= '</div>';
                $modalsHtml .= '</div>';
            }
            $modalsHtml .= '</div>';

        }
  
        // Append modals HTML to the end of article text
        $article->text .= $modalsHtml;
    }

    public function onContentAfterDisplay($context, &$article, &$params, $page = 0)
    {
    }

    public function onContentBeforeSave($context, $article, $isNew)
    {
    }

    public function onContentAfterSave($context, $article, $isNew)
    {
    }

    public function onContentPrepareForm($form, $data)
    {
    }

    public function onContentPrepareData($context, $data)
    {
    }

    public function onContentBeforeDelete($context, $data)
    {
    }

    public function onContentAfterDelete($context, $data)
    {
    }

    public function onContentChangeState($context, $pks, $value)
    {
    }

    public function onContentSearchAreas()
    {
    }

    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
    }
}
