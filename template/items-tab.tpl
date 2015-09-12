<div>
 {include file="table-info.tpl"}
 <table>
  <tbody id="table">
   {foreach from=$items item=i}
   <tr>
    <td class="img"><img src="image.php?guid={$i.imageid}"></img></td>
    <td class="id"><a href="item.php?guid={$i.guid}">{$i.name}</a></td>
    <td>{$i.limitlv}</td>
   </tr>
   {/foreach}
  </tbody>
 </table>
</div>
