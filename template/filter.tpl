<table class="filter">
{foreach from=$types item=t name=i}
{if $smarty.foreach.i.index % 4 == 0}
   <tr>
{/if}
     <td><input type="checkbox" id="filter{$t.id}"{if $t.empty} disabled{/if}><label for="filter{$t.id}">{$t.name}</label></input></td>
{if $smarty.foreach.i.index % 4 == 3}
   </td>
{/if}
{/foreach}
{* XXX can't do math from for loops, so assign here... *}
{assign var=stray_cells value=4-count($types)%4 }
{if $stray_cells < 4}
 {for $n=1 to $stray_cells}
    <td>&nbsp;</td>
 {/for}
{/if}
 </tr>
</table>
