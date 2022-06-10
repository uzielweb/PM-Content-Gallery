<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pm_content_gallery
 *
 * @copyright
 * @license     GNU/Public
 */

defined('_JEXEC') or die;

/**
 * Pm_content_gallery plugin class.
 *
 * @since  2.5
 * @link https://docs.joomla.org/Plugin/Events/Content
 */
class PlgContentPm_content_gallery extends JPlugin
{
    /**
     * onContentPrepare Event
     *
     * @param   string   $context      The context of the content being passed to the plugin.
     * @param   mixed    &$article    The article object.  Note $article->text is also available
     * @param   mixed    &$params      The article params
     * @param   integer  $page         Optional page number. Unused. Defaults to zero.
     *
     * @return  boolean    True on success.
     */
    // public function onContentPrepare($context, &$article, &$params, $page = 0){
    //     return true;
    // }
    /**
     * onContentAfterTitle Event
     *
     * @param   string   $context      The context of the content being passed to the plugin.
     * @param   mixed    &$article    The article object.  Note $article->text is also available
     * @param   mixed    &$params      The article params
     * @param   integer  $page         Optional page number. Unused. Defaults to zero.
     *
     * @return  boolean    True on success.
     */
    // public function onContentAfterTitle($context, &$article,, &$params, $page = 0){
    //     return true;
    // }
    /**
     * onContentBeforeDisplay Event
     *
     * @param   string   $context      The context of the content being passed to the plugin.
     * @param   mixed    &$article    The article object.  Note $article->text is also available
     * @param   mixed    &$params      The article params
     * @param   integer  $page         Optional page number. Unused. Defaults to zero.
     *
     * @return  boolean    True on success.
     */
    // public function onContentBeforeDisplay($context, &$article,, &$params, $page = 0){
    //     return true;
    // }
    /**
     * onContentAfterDisplay Event
     *
     * @param   string   $context      The context of the content being passed to the plugin.
     * @param   mixed    &$article    The article object.  Note $article->text is also available
     * @param   mixed    &$params      The article params
     * @param   integer  $page         Optional page number. Unused. Defaults to zero.
     *
     * @return  boolean    True on success.
     */
    // private function createHtmlOutput()
    // {
    //     $html[$m] = '';
    //     if (!file_exists($this->absolutePath . $this->rootFolder . $this->imagesDir . '/index.html')) {
    //         file_put_contents($this->absolutePath . $this->rootFolder . $this->imagesDir . '/index.html', '');
    //     }
    //     $html[$m] = 'Hello World!';
    // }
    public function onContentPrepare($context, &$article, &$params, $limitstart)
    {

        $doc = JFactory::getDocument();

        $doc->addStyleSheet(JUri::root() . 'plugins/content/pm_content_gallery/assets/css/owl.theme.default.min.css');
        $doc->addStyleSheet(JUri::root() . 'plugins/content/pm_content_gallery/assets/css/owl.carousel.min.css');
        $doc->addStyleSheet(JUri::root() . 'plugins/content/pm_content_gallery/assets/css/temabasico.css');

        //Atribui o Jquery do Joomla
        JHtml::_('jquery.framework', true, true);
        //Carrega o script após o Jquery do Joomla
        // JHtml::_('script', JUri::root() . 'plugins/content/pm_content_gallery/assets/js/owl.carousel.min.js', false, true, false, false);
	     $doc->addScript(JUri::root() . 'plugins/content/pm_content_gallery/assets/js/owl.carousel.min.js');

        //pesquisa no conteúdo
        preg_match_all('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $article->text, $matches);
        $allMatches = $matches[0];
          // prepagara o html (inicia vazio)
       
        $html[] = "";
        foreach ($allMatches as $m=>$match) {
            preg_match_all('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $match, $newmatches);
            $newMatchesContent[$m] = $newmatches[1][0];
           
       
        //armazena os dados obtidos do conteúdo

        $contentArray = explode("|", isset($newMatchesContent[$m]) ? $newMatchesContent[$m] : '');
     
     
        $pasta = isset($contentArray[0]) ? $contentArray[0] : '';
        $descricao = isset($contentArray[1]) ? $contentArray[1] : '';
       

      
      
        
        //envelopa todo o conteúdo para melhor formatação no css depois
        $html[$m] = '';
        $html[$m] .= '<div class="pmcontentgallery gallery-'.$article->id.$m.'">';
        // usaremos o id do artigo para atribuir corretamente o slide
        $html[$m] .= '<div class="owl-carousel carrossel-'.$article->id.$m.' owl-theme">';
        //pega as imagens do diretório
        $directory = $this->params->get('folder', 'images') . '/' . $pasta;

        $files = preg_grep('~\.(jpeg|jpg|png|gif|JPEG|JPG|PNG|GIF)$~', scandir($directory));

        foreach ($files as $k=>$file) {
            $imagem = JUri::base() . $directory . '/' . $file;

            $heightb4 = $this->params->get("height", "16by9");
            $heightb5 = str_replace("by", "x", $heightb4);
            //  get image name without extension
            $imageName = pathinfo($file, PATHINFO_FILENAME);
            $imageName = str_replace("-", " ", $imageName);           
            $imageName = str_replace("_", " ", $imageName);
            $imageName = ucwords($imageName);
            $alt = $descricao ? $descricao . ' - ' . $k : $imageName;
            $html[$m] .= '<div class="item">';
            $html[$m] .= '<div  class="embed-responsive embed-responsive-' . $heightb4 . ' ratio ratio-' . $heightb5 . '">';
            $html[$m] .= '<img src="' . $imagem . '" alt="' . $alt  . '" class="w-100 img-responsive">';
            $html[$m] .= '</div>';
            $html[$m] .= '</div>';

        }
        $html[$m] .= '</div>';
        $html[$m] .= '<div class="descrição">' . $descricao . '</div>';
        $html[$m] .= '</div>';
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
			})
		});
		</script>';
       // change each match to the new html
       $article->text = preg_replace('@{' . $this->params->get("customtagname", "pmgallery") . '}(.*){/' . $this->params->get("customtagname", "pmgallery") . '}@Us', $html[$m], $article->text, 1);
        }
         

    }
       
    public function onContentAfterDisplay($context, &$article, &$params, $page = 0)
    {
        // return true;
    }
    /**
     * onContentBeforeSave Event
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
     * @param   object  $article  A JTableContent object.
     * @param   bool    $isNew    If the content is just about to be created.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentBeforeSave($context, $article, $isNew)
    {
        // return true;
    }
    /**
     * onContentAfterSave Event
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
     * @param   object  $article  A JTableContent object.
     * @param   bool    $isNew    If the content is just about to be created.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterSave($context, $article, $isNew)
    {
        // return true;
    }
    /**
     * Prepare form.
     *
     * @param   JForm  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since    2.5
     */
    public function onContentPrepareForm($form, $data)
    {
        // return true;
    }
    /**
     * Runs on content preparation
     *
     * @param   string  $context  The context for the data
     * @param   object  $data     An object containing the data for the form.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function onContentPrepareData($context, $data)
    {

    }
    /**
     * onContentBeforeDelete Event
     *
     * @param   string  $context  The context for the content passed to the plugin.
     * @param   object  $data     The data relating to the content that was deleted.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function onContentBeforeDelete($context, $data)
    {
        // return true;
    }
    /**
     * onContentAfterDelete Event
     *
     * @param   string  $context  The context for the content passed to the plugin.
     * @param   object  $data     The data relating to the content that was deleted.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function onContentAfterDelete($context, $data)
    {
        // return true;
    }
    /**
     * onContentChangeState Event
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      A list of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentChangeState($context, $pks, $value)
    {

    }
    /**
     * onContentSearch Event
     *
     * The SQL must return the following fields that are used in a common display
     * routine: href, title, section, created, text, browsernav.
     *
     * @param   string  $text      Target search string.
     * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
     * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
     * @param   mixed   $areas     An array if the search is to be restricted to areas or null to search all areas.
     *
     * @return  array  Search results.
     *
     * @since   1.6
     */
    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        // return [];
    }
    /**
     * Determine areas searchable by this plugin.
     *
     * @return  array  An array of search areas.
     *
     * @since   1.6
     */
    public function onContentSearchAreas()
    {
        // return [];
    }
}
