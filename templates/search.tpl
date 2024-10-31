<div id="search_form">
        <h4>Search for a {$search_term}</h4>
        <form method="GET" action="{if isset($search_uri)}{$search_uri}{else}.{/if}">
        <input type="text" placeholder="Enter State" name="state" id='booster-rocket-state' list='booster-rocket-state-list' maxlength="30" value="{if isset($state)}{$state}{/if}"><br />
		<datalist id='booster-rocket-state-list'>
			{foreach from=$state_list item=state}<option value="{$state['name']}" />{/foreach}
		</datalist>
        <input type="text" placeholder="Enter Town" name="town" id="booster-rocket-city" list='booster-rocket-city-list' value="{if isset($town)}{$town}{/if}"><br />
		<datalist id='booster-rocket-city-list'>
			{foreach from=$city_list item=city}<option value="{$city['name']}" />{/foreach}
		</datalist>
        <input type="submit" style="width: 218px;" value="SEARCH">
        </form>
</div>

{if isset($powered_by)}<div id="notice">{$powered_by}</div>{/if}
<br />
{include file="spinner.tpl"}
