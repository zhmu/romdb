<table class="limit">
{foreach from=$limits item=l name=i}
{if $smarty.foreach.i.index % 4 == 0}
   <tr>
{/if}
     <td><a href="javascript:;" id="limit{$l.id}">{$l.name}</a></td>
{if $smarty.foreach.i.index % 4 == 3}
   </tr>
{/if}
{/foreach}
{* XXX can't do math from for loops, so assign here... *}
{assign var=stray_cells value=4-count($limits)%4 }
{if $stray_cells < 4}
 {for $n=1 to $stray_cells}
    <td>&nbsp;</td>
 {/for}
{/if}
 </tr>
</table>
