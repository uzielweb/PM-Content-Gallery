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
    private function generateThumbnail($filePath, $thumbPath, $width = 300)
{
    // Verifica se a miniatura já existe
    if (file_exists($thumbPath)) {
        return $thumbPath;
    }

    // Obtém informações da imagem
    list($originalWidth, $originalHeight, $imageType) = getimagesize($filePath);

    // Calcula a altura proporcional
    $height = intval($originalHeight * ($width / $originalWidth));

    // Cria uma nova imagem
    $thumbnail = imagecreatetruecolor($width, $height);

    // Cria a imagem original com base no tipo
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filePath);
            break;
        default:
            return false; // Tipo de imagem não suportado
    }

    // Redimensiona e salva a miniatura
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
    imagejpeg($thumbnail, $thumbPath, 90); // Salva como JPEG de alta qualidade
    imagedestroy($thumbnail);
    imagedestroy($source);

    return $thumbPath;
}

    public function onContentPrepare($context, &$article, &$params, $limitstart)
{
    // Registering necessary CSS and JS for Bootstrap and Owl Carousel
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

    // Get parameter values
    $galleryType = $this->params->get('gallery_type', 'grid'); // Grid or Carousel
    $imagesPerRow = $this->params->get('images_per_row', 3); // Columns per row
    $modalEnabled = $this->params->get('modal', 1); // Whether modal is enabled
    $imageRatio = $this->params->get('image_ratio', '16by9'); // Aspect ratio

    preg_match_all('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $article->text, $matches);
    $allMatches = $matches[0];
    $html[] = "";
    $modalsHtml = ""; // String to store modals HTML separately

    foreach ($allMatches as $m => $match) {
        preg_match_all('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $match, $newmatches);
        $newMatchesContent[$m] = $newmatches[1][0];
        $contentArray = explode("|", isset($newMatchesContent[$m]) ? $newMatchesContent[$m] : '');
        $pasta = isset($contentArray[0]) ? $contentArray[0] : '';
        $descricao = isset($contentArray[1]) ? $contentArray[1] : '';

        $html[$m] = '';
        $html[$m] .= '<section class="pmcontentgallery gallery-'.$article->id.$m.'">';

        if ($galleryType == 'grid') {
            // Grid Layout
            $html[$m] .= '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-'.$imagesPerRow.'">';
        } else {
            // Carousel Layout
            $html[$m] .= '<div class="owl-carousel carrossel-'.$article->id.$m.' owl-theme">';
        }

        $directory = $this->params->get('folder', 'images') . '/' . $pasta;
        if (is_dir($directory)) {
            $files = preg_grep('~\.(jpeg|jpg|png|gif|JPEG|JPG|PNG|GIF)$~', scandir($directory));
        } else {
            echo "The directory doesn't exist: $directory";
        }

        foreach ($files as $k => $file) {
            $imagem = Uri::base() . $directory . '/' . $file;
            $heightb4 = $imageRatio; // Dynamically use selected ratio
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
            
            if ($galleryType == 'grid') {
                $html[$m] .= '<div class="col">';
            } else {
                $html[$m] .= '<div class="item">';
            }
            
            $html[$m] .= '<div class="embed-responsive embed-responsive-' . $heightb4 . ' ratio ratio-' . $heightb5 . ' ' . $imageOrientation . '">';
            if ($modalEnabled) {
                $html[$m] .= '<a href="#" data-toggle="modal" data-target="#galleryModal-' . $article->id . '-' . $m . '-' . $k . '" rel="gallery-' . $article->id . '-' . $m . '" data-bs-toggle="modal" data-bs-target="#galleryModal-' . $article->id . '-' . $m . '-' . $k . '" rel="gallery-' . $article->id . '-' . $m . '" gallery="' . $article->id . '-' . $m . '">';
            }
           $thumbPath = JPATH_ROOT . '/images/pmgallerythumbs/' . basename($file);
            $thumbUrl = Uri::base() . 'images/pmgallerythumbs/' . basename($file);

            // Gera a miniatura se necessário
            $this->generateThumbnail($directory . '/' . $file, $thumbPath);

            // Usa a URL da miniatura no lazy load
            $html[$m] .= '<img class="img-fluid rounded shadow lazy" loading="lazy" data-src="' . $thumbUrl . '" alt="' . $alt . '" src="'.$thumbUrl.'">';


            if ($modalEnabled) {
                $html[$m] .= '</a>';
            }
            $html[$m] .= '</div>';
            $html[$m] .= '</div>';
        }

        if ($galleryType == 'grid') {
            $html[$m] .= '</div>'; // Close Bootstrap row
        } else {
            $html[$m] .= '</div>'; // Close Owl Carousel
        }
        $html[$m] .= '<div class="description">' . $descricao . '</div>';
        $html[$m] .= '</section>';
        $article->text = preg_replace('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $html[$m], $article->text, 1);
    }

    // Add modal gallery if enabled
    if ($modalEnabled) {
        $modalsHtml = '<div class="pm-modal-gallery pm-modal-gallery-' . $article->id . ' position-relative">';
        foreach ($files as $k => $file) {
            $imagem = Uri::base() . $directory . '/' . $file;
            $heightb4 = $imageRatio;
            $heightb5 = str_replace("by", "x", $heightb4);
            $imageName = pathinfo($file, PATHINFO_FILENAME);
            $imageName = str_replace(["-", "_"], " ", $imageName);
            $imageName = ucwords($imageName);
            $alt = $descricao ? $descricao . ' - ' . $k : $imageName;
           
            $modalsHtml .= '<div class="modal fade" id="galleryModal-' . $article->id . '-' . $m . '-' . $k . '" tabindex="-1" aria-labelledby="galleryModalLabel-' . $article->id . '-' . $m . '-' . $k . '" aria-hidden="true">';
            $modalsHtml .= '<div class="modal-dialog modal-lg" role="document">';
            $modalsHtml .= '<div class="modal-content">';
            $modalsHtml .= '<div class="modal-body">';
            $modalsHtml .= '<img src="' . $imagem . '" class="w-100" alt="' . $alt . '">';
            $modalsHtml .= '</div>';
            $modalsHtml .= '</div>';
            $modalsHtml .= '</div>';
            $modalsHtml .= '</div>';
        }
        $article->text .= $modalsHtml;
    }
}

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
