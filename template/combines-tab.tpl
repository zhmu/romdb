<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$combines item=i}
   <tr id="guid{$i.guid}">
    <td class="img">&nbsp;</td>
    <td class="id"><a id="link{$i.guid}" href="javascript:;">{$i.name}</a></td>
   </tr>
   {/foreach}
  </tbody>
 </table>
</div>
