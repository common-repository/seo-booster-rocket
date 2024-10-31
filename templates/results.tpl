<div id="container" style="display: table; width: 100%">
  <div id="row" style="display: table-row; width: 100%">
   <div id="left" style="display: table-cell; width: 15%;">
	{include file="search.tpl"}
    {if isset($notice_google)}<h5 id="notice">Google: {$notice_google}</h5>{/if}
    {if isset($notice_yelp)}<h5 id="notice">Yelp: {$notice_yelp}</h5>{/if}
    {if isset($notice_facebook)}<h5 id="notice">Facebook: {$notice_facebook}</h5>{/if}
    <h5 id="notice">Number of Results: {$results_combined|count}</h5>
    <div id="notice">
	<a href="#results">Take me to the Results!</a>
    </div>
   </div>
   <div id="right" style="display: table-cell; width: 80%;">
	<h5>Map of Results</h5>
	{if isset($maps_api_key)}{include file="maps.tpl"}{else}You must input a Google Maps API Key before the SEO Booster Rocket Map will work!{/if}
   </div>
  </div>
</div>

{if isset($results_combined)}

<br />
	<a name="results"></a>
	<table >
	<tr id="rocket-header">
		<th>Name</th>
		<th>Address</th>
		<th>Rating</th>
		<th>Phone</th>
		<th>Photos/Misc</th>
	</tr>

{foreach from=$results_combined item=result name=count}
	<a name="{$result['id']}"></a>
	<tr class="rocket-results">
                <td>{$result['name']}</td>
		<td>
			{if isset($result['url'])}
				<a rel="nofollow" href="{$result['url']}" target="_blank">{$result['address']}</a>
			{else}
				<a rel="nofollow" href="https://maps.google.com/maps/place/{$result['name']|escape}/@{$result['latitude']},{$result['longitude']}" target="_blank">{$result['address']}</a>
			{/if} 
				</td>
		<td>{math equation="x/y" x=array_sum($result['rating']) y=count($result['rating'])}</td>
		<td nowrap>{$result['phone']}</td>
		<td>
			{if isset($result['photos'])}{$result['photos']}{/if}
			{if isset($result['website'])}<br />{$result['website']}{/if}
				</td>
	</tr>
{foreachelse}
	<tr><td>No Results were Found. Please Try Again</td></tr>
{/foreach}

</table>
{/if}
