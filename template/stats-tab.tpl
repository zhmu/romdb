<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$stats item=i}
   <tr id="guid{$i.guid}">
    <td class="img"><img src="image.php?guid={$i.imageid}"></img></td>
    <td class="id"><a class="q{$i.rare}" id="link{$i.guid}" href="javascript:;">{$i.name}</a></td>
    <td>{$i.rarity}</td>
   </tr>
   {/foreach}
  </tbody>
 </table>
</div>
