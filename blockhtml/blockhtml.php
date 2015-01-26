<?php

class BlockHTML extends Module
{
/* example, in index.tpl : 
		{hook h='displayHome' mod='blockhtml' blonk="info"}
		<hr/>
		{hook h='displayHome' mod='blockhtml' blonk="presse"}
*/
	private $blohks=array(
		"top"=>array("css"=>"top","hook"=>"top","blonks"=>array()),
		"home"=>array("css"=>"home","hook"=>"displayHome","blonks"=>array(
			"info","press1","press2","press3","press4")),
		"footer"=>array("css"=>"footer","hook"=>"displayFooter","blonks"=>array()),
		"left"=>array("css"=>"left","hook"=>"leftColumn","blonks"=>array()),
		"right"=>array("css"=>"right","hook"=>"rightColumn","blonks"=>array()),
	);
	function __construct()
	{
		$this->name = 'blockhtml';
		$this->tab = 'Blocks';
		$this->version = '0.2';

		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Block HTML');
		$this->description = $this->l('Adds a block with free HTML code');
	}

	function install()
	{
		if (!parent::install())
			return false;
		$this->registerHook("header");
		foreach($this->blohks as $blohk)
			$this->registerHook($blohk["hook"]);
		return true;
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookhtml($params,$blohk)
	{
		if (!file_exists(dirname(__FILE__).'/values.xml')) return false;
		$xml = simplexml_load_file(dirname(__FILE__).'/values.xml');
		if (!$xml) return false;
		global $cookie, $smarty;
		$bloktt=array();
		foreach($blohk["blonks"] as $blonk)
			if((!isset($params["blonk"])) or ($params["blonk"] == $blonk) )
				if ($xml->{$blohk["css"].$blonk.'_'.$cookie->id_lang})
					$bloktt[]=array(
					'idcss' => $blohk["css"].$blonk,
					'idcsslang' => $blohk["css"].$blonk.'_'.$cookie->id_lang,
					'text' => $xml->{$blohk["css"].$blonk.'_'.$cookie->id_lang},
					'this_path' => $this->_path
					);
		$smarty->assign(array('blockhtml' => $bloktt));
		return $this->display(__FILE__, 'blockhtml.tpl');
	}

	public function hookTop($params){return($this->hookhtml($params,$this->blohks["top"]));}
	public function hookHome($params){return($this->hookhtml($params,$this->blohks["home"]));}
	public function hookDisplayHome($params){return($this->hookhtml($params,$this->blohks["home"]));}
	public function hookDisplayFooter($params){return($this->hookhtml($params,$this->blohks["footer"]));}
	public function hookLeftColumn($params){return($this->hookhtml($params,$this->blohks["left"]));}
	public function hookRightColumn($params){return($this->hookhtml($params,$this->blohks["right"]));}
	public function hookHeader($params) {
		$this->context->controller->addJS(_PS_JS_DIR_."tiny_mce/tiny_mce.js");
	}

	function getContent()
	{
		/* display the module name */
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		/* update the editorial xml */
		if (isset($_POST['submitUpdate']))
		{
			// Forbidden key
			$forbidden = array('submitUpdate');

			// Generate new XML data
			$newXml = '<?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
			$newXml .= '<html>'."\n";
			// Making header data
			foreach ($_POST AS $key => $field)
			{
				if (_PS_MAGIC_QUOTES_GPC_)
					$field = stripslashes($field);
				if ($line = $this->putContent($newXml, $key, $field, $forbidden, 'header'))
					$newXml .= $line;
			}
			$newXml .= "\n</html>\n";

			/* write it into the editorial xml file */
			if ($fd = @fopen(dirname(__FILE__).'/values.xml', 'w'))
			{
				if (!@fwrite($fd, $newXml))
					$this->_html .= $this->displayError($this->l('Unable to write to the text file.'));
				if (!@fclose($fd))
					$this->_html .= $this->displayError($this->l('Can\'t close the text file.'));
			}
			else
				$this->_html .= $this->displayError($this->l('Unable to update the text file.<br />Please check the text file\'s writing permissions.'));
		}

		/* display the editorial's form */
		$this->_displayForm();

		return $this->_html;
	}

	private function _displayForm()
	{
		/* Languages preliminaries */
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$iso = Language::getIsoById($defaultLanguage);
		$divLangName = 'text_left??text_right';

		/* xml loading */
		$xml = false;
		if (file_exists(dirname(__FILE__).'/values.xml'))
				if (!$xml = @simplexml_load_file(dirname(__FILE__).'/values.xml'))
					$this->_html .= $this->displayError($this->l('Your text file is empty.'));

		$this->_html .= '
		<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
					tinyMCE.init({
						mode : "textareas",
						theme : "advanced",
						plugins : "style,layer,table,image,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,fullscreen,code,colorpicker",
						//unused plugins : "safari,pagebreak,style,layer,table,image,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen,code",
						// Theme options (useless?)
						theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
						theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
						theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
						theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : false,
						content_css : "'.__PS_BASE_URI__.'themes/'._THEME_NAME_.'/css/global.css",
						document_base_url : "'.__PS_BASE_URI__.'",
						width: "600",
						height: "400",
						font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
						// Drop lists for link/image/media/template dialogs
						template_external_list_url : "lists/template_list.js",
						external_link_list_url : "lists/link_list.js",
						external_image_list_url : "lists/image_list.js",
						media_external_list_url : "lists/media_list.js",
						elements : "nourlconvert",
						entity_encoding: "raw",
						convert_urls : false,
						language : "'.(file_exists(_PS_ROOT_DIR_.'/js/tinymce/jscripts/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en').'"
						
					});
		</script>
		';
		$this->_html .= '
		<script language="javascript">id_language = Number('.$defaultLanguage.');</script>
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.'</legend>';

		foreach($this->blohks as $tblohk)
		{
			$blohk=$tblohk["css"];
			foreach($tblohk["blonks"] as $blonk)
			{
				$this->_html .= '
					<label>'.$this->l($blohk.' text').' '.$blonk.'</label>
					<div class="margin-form" >';
				foreach ($languages as $language)
				{
					$blohkid=$blohk.$blonk.'_'.$language['id_lang'];
					$blohkshow=($language['id_lang'] == $defaultLanguage) ? 'block' : 'none';
					$this->_html .= '
						<div id="text_'.$blohkid.'" style="display: '.$blohkshow.';float: left;">
							<textarea cols="64" rows="10" id="'.$blohkid.'" name="'.$blohkid.'">'.
							($xml ? stripslashes(htmlspecialchars($xml->{$blohkid})) : '').'</textarea>
						</div>';
				}
				$this->_html .= 
					$this->displayFlags($languages, $defaultLanguage, $divLangName, 'text_'.$blohk.$blonk, true).'
					<p class="clear">'.$this->l('Text of your choice').'</p>
				</div>';
			}
		}
			
		$this->_html .= '
			<div class="clear pspace"></div>
			<div class="margin-form clear"><input type="submit" name="submitUpdate" value="'.$this->l('Update the text').'" class="button" /></div>
			</fieldset>
		</form>';
	}

	function putContent($xml_data, $key, $field, $forbidden)
	{
		foreach ($forbidden AS $line)
			if ($key== $line) return 0;
		$field = htmlspecialchars($field);
		if (!$field) return 0;
		return ("\n".'		<'.$key.'>'.$field.'</'.$key.'>');
	}
}
?>
