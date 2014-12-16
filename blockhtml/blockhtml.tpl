<!-- Block HTML module -->
{foreach from=$blockhtml item=blockhtmldiv}
<div class="blockhtml blockhtml_{$blockhtmldiv.idcsslang}" id="blockhtml_{$blockhtmldiv.idcss}">{$blockhtmldiv.text}</div>
{/foreach}
<!-- /BlockHTML module -->
