<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$cards item=c}
   <tr id="guid{$c.guid}">
    <td class="img"><img src="image.php?guid={$c.imageid}"></img></td>
    <td class="id"><a class="q{$c.rare}" id="link{$c.guid}" href="javascript:;">Card - {$c.name}</a></td>
   </tr>
   {/foreach}
  </tbody>
 </table>
</div>
