prestashop-mod-blockhtml
========================

prestashop module to display html content block in prestashop front office

Module used when in a prestashop front page you want to put some html
content, but easily editable. edito, annoncement, footer, top message,
things like that.

This is an evolution from a module called blockhtml. You can add any
number of blocks, any name, on many hooks.

INSTALL
-------

import zipped folder
or copy to modules/

make *.xml writable (esp. values.xml = your texts)

EXAMPLE
-------

In index.tpl call the hook, give block name (none=>all) : 
`
		{hook h='displayHome' mod='blockhtml' blonk="info"}
		<hr/>
		{hook h='displayHome' mod='blockhtml' blonk="press"}
`

configure blocks list in blockhtml.php
choose which hook (don't change name)
then css prefix, real hook name, and list of blocks
`
	private $blohks=array(
		"top"=>array("css"=>"top","hook"=>"top","blonks"=>array("newsmsg")),
		"home"=>array("css"=>"home","hook"=>"displayHome","blonks"=>array("info","press")),
		"footer"=>array("css"=>"footer","hook"=>"displayFooter","blonks"=>array("adr")),
		"left"=>array("css"=>"left","hook"=>"leftColumn","blonks"=>array()),
		"right"=>array("css"=>"right","hook"=>"rightColumn","blonks"=>array("myads")),
	);
`

TODO
----
main config is at top of php file, it should be config editable.


