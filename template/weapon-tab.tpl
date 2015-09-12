<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$weapons item=w}
   <tr id="guid{$w.guid}">
    <td class="img"><img src="image.php?guid={$w.imageid}"></img></td>
    <td class="id"><a id="link{$w.guid}" class="q{$w.rare}" href="javascript:;">{$w.name}</a></td>
    <td>{$w.limitlv}</td>
    <td>{$w.type}</td>
   </tr>
   {foreachelse}
    <td class="noresults" colspan="4">No results</td>
   {/foreach}
  </tbody>
 </table>
</div>
