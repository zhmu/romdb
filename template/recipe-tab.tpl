<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$recipes item=a}
   <tr id="guid{$a.guid}">
    <td class="img"><img src="image.php?guid={$a.imageid}"></img></td>
    <td class="id"><a id="link{$a.guid}" class="q{$a.rare}" href="javascript:;">Recipe: {$a.name}</a></td>
    <td>{$a.requestskilllv}</td>
    <td class="hover">
     {foreach from=$a.items item=i}
      <img src="image.php?guid={$i.imageid}"/>
     {/foreach}
    </td>
   </tr>
   {foreachelse}
    <td class="noresults" colspan="4">No results</td>
   {/foreach}
  </tbody>
 </table>
</div>
