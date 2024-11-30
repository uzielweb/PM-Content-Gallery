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
class PlgContentPm_content_gallery extends CMSPlugin
{
    public function resizeImage($file, $w, $h)
{
    // Detectar o tipo de imagem
    $info = getimagesize($file);
    $mime = $info['mime'];

    // Escolher a função correta para criar a imagem com base no tipo
    switch ($mime) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($file);
            break;
        case 'image/png':
            $src = imagecreatefrompng($file);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($file);
            break;
        case 'image/webp':
            $src = imagecreatefromwebp($file);
            break;
        case 'image/bmp':
            $src = imagecreatefrombmp($file);
            break;
        case 'image/jpg':
            $src = imagecreatefromjpeg($file);
            break;
        default:
            throw new \Exception("Unsupported image format: $mime");
    }

    // Continuar com o redimensionamento
    list($width, $height) = $info;
    $r = $width / $height;
    if ($w / $h > $r) {
        $newwidth = $h * $r;
        $newheight = $h;
    } else {
        $newheight = $w / $r;
        $newwidth = $w;
    }

    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    // Retornar a imagem redimensionada
    return $dst;
}
public function cropImage($file, $size)
{
    // Detectar o tipo de imagem
    $info = getimagesize($file);
    $mime = $info['mime'];

    // Escolher a função correta para criar a imagem com base no tipo
    switch ($mime) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($file);
            break;
        case 'image/png':
            $src = imagecreatefrompng($file);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($file);
            break;
        case 'image/webp':
            $src = imagecreatefromwebp($file);
            break;
        case 'image/bmp':
            $src = imagecreatefrombmp($file);
            break;
        case 'image/jpg':
            $src = imagecreatefromjpeg($file);
            break;
        default:
            throw new \Exception("Unsupported image format: $mime");
    }

    // Continuar com o crop
    list($width, $height) = $info;
    $minSide = min($width, $height);
    $srcX = ($width - $minSide) / 2;
    $srcY = ($height - $minSide) / 2;

    $dst = imagecreatetruecolor($size, $size);
    imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $minSide, $minSide);

    // Retornar a imagem cortada
    return $dst;
}

    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {
        if (version_compare(JVERSION, '4.0', '>=')) {
            $wa = Factory::getDocument()->getWebAssetManager();
          
            if (!$wa->assetExists('script', 'joomla.jquery')) {
                $wa->registerAndUseScript('joomlajquery', Uri::root(true) . 'media/vendor/jquery/js/jquery.min.js', ['version' => 'auto', 'relative' => true]);
            }
            if (!$wa->assetExists('script', 'joomla.jquery-migrate')) {
                $wa->registerAndUseScript('joomla.jquery-migrate', Uri::root(true) . 'media/vendor/jquery-migrate/js/jquery-migrate.min.js', ['version' => 'auto', 'relative' => true]);
            }
              // check if exists bootstrap in the template and if not, load it
            if (!$wa->assetExists('script', 'bootstrap')) {
                $wa->registerAndUseScript('bootstrap.bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@latest/dist/js/bootstrap.bundle.min.js', [], ['defer' => true]);
                // inser script declaration
                $wa->registerAndUseInlineScript('modal.active', 'jQuery(document).ready(function($) {
                $(".modal").on("shown.bs.modal", function () {
                    console.log("Modal shown");
                    });
                });', ['defer' => true]);
            }
            if (!$wa->assetExists('style', 'bootstrap.css')) {
                $wa->registerAndUseStyle('bootstrap.css', 'https://cdn.jsdelivr.net/npm/bootstrap@latest/dist/css/bootstrap.min.css');
            }
            if ($this->params->get("gallery_type", "owl_carousel") == "owl_carousel") {   
            $wa->registerAndUseStyle('pm_content_gallery_default', Uri::root(true) . 'plugins/content/pm_content_gallery/assets/css/owl.theme.default.min.css', ['version' => 'auto', 'relative' => true]);
            $wa->registerAndUseStyle('pm_content_gallery_carousel', Uri::root(true) . 'plugins/content/pm_content_gallery/assets/css/owl.carousel.min.css', ['version' => 'auto', 'relative' => true]);
            $wa->registerAndUseScript('pm_content_gallery_js', Uri::root(true) . 'plugins/content/pm_content_gallery/assets/js/owl.carousel.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
            }
            $wa->registerAndUseStyle('pm_content_gallery_temabasico', Uri::root(true) . 'plugins/content/pm_content_gallery/assets/css/temabasico.css', ['version' => 'auto', 'relative' => true]);
        } else {
            $doc = Factory::getDocument();
         
            if ($this->params->get("gallery_type", "owl_carousel") == "owl_carousel") {             
            $doc->addStyleSheet(Uri::root(true) . 'plugins/content/pm_content_gallery/assets/css/owl.theme.default.min.css');
            $doc->addStyleSheet(Uri::root(true) . 'plugins/content/pm_content_gallery/assets/css/owl.carousel.min.css');            
            }
            $doc->addStyleSheet(Uri::root(true) . 'plugins/content/pm_content_gallery/assets/css/temabasico.css');
            if ($this->params->get("gallery_type", "owl_carousel") == "owl_carousel") {  
            HTMLHelper::_('jquery.framework', true, true);
            $doc->addScript(Uri::root(true) . 'plugins/content/pm_content_gallery/assets/js/owl.carousel.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
            }
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
            $directory = $this->params->get('folder', 'images') . '/' . $pasta;       
            if (is_dir($directory)) {
                $files = preg_grep('~\.(jpeg|jpg|png|webp|gif|JPEG|JPG|PNG|WEBP|GIF)$~', scandir($directory));
            } else {
                echo "The directory doesn't exist: $directory";
            }
            $html[$m] = '';
            $html[$m] .= '<section class="pmcontentgallery gallery-'.$article->id.$m.'">';
           // Obtem o tipo de galeria
$galleryType = $this->params->get("gallery_type", "owl_carousel");

// Inicializa a variável de classes e atributos
$carouselClass = '';
$carouselAttributes = '';

// Configurações baseadas no tipo de galeria
switch ($galleryType) {
    case "owl_carousel":
        $carouselClass = 'owl-carousel carrossel-' . $article->id . $m . ' owl-theme';
        break;

    case "bootstrap_carousel":
        $carouselClass = 'carousel slide';
        $carouselAttributes = ' id="carousel-' . $article->id . $m . '" data-bs-ride="carousel"';
        break;

    default:
        $carouselClass = 'row';
        break;
}

// Monta o HTML principal
$html[$m] .= '<div class="' . $carouselClass . '"' . $carouselAttributes . '>';

// Adiciona a div carousel-inner apenas para bootstrap_carousel
if ($galleryType == "bootstrap_carousel") {
    $html[$m] .= '<div class="carousel-indicators">';
foreach ($files as $k => $file) {
    // Corrigido o índice para o indicador de slide
    $html[$m] .= '<button type="button" data-bs-target="#carousel-' . $article->id . $m . '" data-bs-slide-to="' . $k-2 . '" ' . ($k == 2 ? 'class="active" aria-current="true"' : '') . ' aria-label="Slide ' . ($k-1) . '"></button>';
}
$html[$m] .= '</div>';
$html[$m] .= '<div class="carousel-inner">';

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
                $thumbnailWidth = $this->params->get("thumbnail_width", "300");
                $thumbnailHeight = $this->params->get("thumbnail_height", "300");
                $imageRatio = $imageWidth / $imageHeight;
                $imageOrientation = $imageRatio > 1 ? 'landscape' : 'portrait';
                // generate thumbnail image
                $thumbnail = $directory . '/thumbnail/' . pathinfo($file, PATHINFO_FILENAME) . '_' . $thumbnailWidth . 'x' . $thumbnailHeight . '.' . pathinfo($file, PATHINFO_EXTENSION);
                if (!file_exists($directory . '/thumbnail')) {
                    mkdir($directory . '/thumbnail', 0777, true);
                }
                if (!file_exists($thumbnail)) {
                    // use joomla default image resize function
                    // create function to resize image
                    $image = $this->cropImage($directory . '/' . $file, $thumbnailWidth, $thumbnailHeight);
                    // save image
                    imagejpeg($image, $thumbnail);
                }
                if (!is_dir($directory)) {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('PLG_CONTENT_PM_CONTENT_GALLERY_DIRECTORY_NOT_FOUND', $directory), 'error');
                    continue;
                }
                $html[$m] .= '<div class="' . ($galleryType == "bootstrap_carousel" ? 'carousel-item ' . ($k == 2 ? 'active' : '') : 'item ' . ($galleryType == "owl_carousel" ? 'item-' . $article->id . $m . '-' . $k : 'col-md-' . round(12 / $this->params->get("images_per_row")) . ' mb-3')) . '">';
                $html[$m] .= '<div class="overflow-hidden embed-responsive embed-responsive-' . $heightb4 . ' ratio ratio-' . $heightb5 . ' ' . $imageOrientation . '">';
                if ($this->params->get("modal", "1") == "1") {
                    $html[$m] .= '<div class="modal-toggle" data-bs-toggle="modal" data-bs-target="#galleryModal-' . $article->id . '-' . $m . '-' . $k . '" rel="gallery-' . $article->id . '-' . $m . '" gallery="' . $article->id . '-' . $m . '">';
                }
                if ($this->params->get("gallery_type", "owl_carousel") == "grid") {
                    $html[$m] .= HTMLHelper::_('image', $thumbnail, $alt, ['class' => 'embed-responsive-item img-fluid']);
                } else {
                    $html[$m] .= HTMLHelper::_('image', $imagem, $alt, ['class' => 'embed-responsive-item img-fluid']);
                }
                  if ($this->params->get("modal", "1") == "1") {
                    $html[$m] .= '</div>';
                }
                $html[$m] .= '</div>';
                $html[$m] .= '</div>';
            }
        
            if ($this->params->get("gallery_type", "owl_carousel") == "bootstrap_carousel") {
                $html[$m] .= '</div>';
                $html[$m] .= '<button class="carousel-control-prev" type="button" data-bs-target="#carousel-' . $article->id . $m . '" data-bs-slide="prev">';
                $html[$m] .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
                $html[$m] .= '<span class="visually-hidden">Previous</span>';
                $html[$m] .= '</button>';
                $html[$m] .= '<button class="carousel-control-next" type="button" data-bs-target="#carousel-' . $article->id . $m . '" data-bs-slide="next">';
                $html[$m] .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
                $html[$m] .= '<span class="visually-hidden">Next</span>';
                $html[$m] .= '</button>';
            }
            $html[$m] .= '</div>';
            $html[$m] .= '<div class="description">' . $descricao . '</div>';
            $html[$m] .= '</section>';
            if ($this->params->get("gallery_type", "owl_carousel") == "owl_carousel") {  
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
            }
            elseif ($this->params->get("gallery_type", "owl_carousel") == "bootstrap_carousel") {
                $html[$m] .= '<script>
                jQuery(document).ready(function($){
                    $("#carousel-' . $article->id . $m . '").carousel({
                        interval: ' . $this->params->get("interval", "5000") . ',
                    });
                });
            </script>';
            }
            // Append modals HTML to the end of article text
            $article->text = preg_replace('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $html[$m], $article->text, 1);
        }
     
        if ($this->params->get("modal", "1") == "1") {
            
            $modalsHtml = '<div class="pm-modal-gallery pm-modal-gallery-' . $article->id .' position-relative">';
        foreach ($files as $k => $file) {
          
            $imagem = Uri::base() . $directory . '/' . $file;
            $heightb4 = $this->params->get("height", "16by9");
            $heightb5 = str_replace("by", "x", $heightb4);
            $imageName = pathinfo($file, PATHINFO_FILENAME);
            $imageName = str_replace(["-", "_"], " ", $imageName);
            $imageName = ucwords($imageName);
            $alt = $descricao ? $descricao . ' - ' . $k : $imageName;
            $modalsHtml .= '<div class="modal fade" id="galleryModal-' . $article->id . '-' . $m . '-' . $k . '" tabindex="-1" aria-labelledby="galleryModalLabel-' . $article->id . '-' . $m . '-' . $k . '" aria-hidden="true">';
            $modalsHtml .= '<div class="modal-dialog modal-dialog-centered modal-xl">';
            
            $modalsHtml .= '<div class="modal-content">';
            $modalsHtml .= '<div class="modal-header">';
            $modalsHtml .= '<h5 class="modal-title" id="galleryModalLabel-' . $article->id . '-' . $m . '-' . $k . '">' . $alt . '</h5>';
            $modalsHtml .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>';
            $modalsHtml .= '</div>';            
            $modalsHtml .= '<div class="modal-body position-relative">';
            $modalsHtml .= HTMLHelper::_('image', $imagem, $alt, ['class' => 'img-fluid']);
            // Botão Previous
            $modalsHtml .= '<button class="btn btn-secondary btn-prev" data-bs-target="#galleryModal-' . $article->id . '-' . $m . '-' . ($k - 1) . '" data-bs-toggle="modal" ' . ($k == 2 ? 'disabled' : '') . '><i class="fas fa-chevron-left"></i></button>';
            // Botão Next
            $modalsHtml .= '<button class="btn btn-secondary btn-next" data-bs-target="#galleryModal-' . $article->id . '-' . $m . '-' . ($k + 1) . '" data-bs-toggle="modal" ' . ($k == count($files) + 1 ? 'disabled' : '') . '><i class="fas fa-chevron-right"></i></button>';
         
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
