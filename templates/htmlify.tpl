<br />
<h3 style="text-align: center;">Search for {$search_term} by {$title}</h3>
<div class="div_table">
{foreach from=$results item=result name=result_list}
	{if $smarty.foreach.result_list.index % $mod == 0}
		{if $smarty.foreach.result_list.index != 0}
			</div>
		{/if}

	<div class="div_row">
	{/if}
			<div class="div_cell"><a href="{$result['url']|escape:" ":"+"}">{$result['name']}</a></div>
	{if $smarty.foreach.result_list.last}
                </div>
	{/if}
{/foreach}

</div>
{if isset($powered_by)}{$powered_by}{/if}

<style>
.div_table {
        display: table;
        border: 1px solid;
        border-radius: 5px;
        width: 100%;
}       
.div_row {
        display: table-row;
        border: 1px solid;
}       
.div_cell {
        display: table-cell;
        padding: 3px;
        border: 1px solid;
        text-align: center;
}       
</style>
