<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$armors item=a}
   <tr id="guid{$a.guid}">
    <td class="img"><img src="image.php?guid={$a.imageid}"></img></td>
    <td class="id"><a id="link{$a.guid}" class="q{$a.rare}" href="javascript:;">{$a.name}</a></td>
    <td>{$a.limitlv}</td>
    <td>{$a.pos}</td>
   </tr>
   {foreachelse}
    <td class="noresults" colspan="4">No results</td>
   {/foreach}
  </tbody>
 </table>
</div>
