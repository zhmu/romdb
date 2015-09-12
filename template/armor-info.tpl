<div>
 <div id="content">
  <div class="guid" id="guid{$i.guid}"></div>
  <div class="item">
   <span class="q{$i.rare}">{$i.name}{if $i.refinement>0} +{$i.refinement}{/if}</span>
   <div class="line">
    <span class="left">Tier {$i.tier}</span>
    <span class="right">Requires Level {$i.limitlv}</span>
   </div>
   <div class="line">
    <span class="left">{$i.type}</span>
    <span class="right">{$i.position}</span>
   </div>
   <div class="line">
    <span class="left">Physical Defense {$i.pdef}</span>
  {if isset($i.mdef)}
    <span class="right">Magic Defense {$i.mdef}</span>
  {/if}
   </div>
   <div class="line">
    <span class="left">Worth: {$i.cost} gold</span>
   </div>
   {foreach from=$i.attrs item=a}
   <div class="ghoststat">+{$a.value} {$a.name}</div>
   {/foreach}
  </div>
  <br/>
  <table class="refinement">
   <tbody>
{for $n=1 to 10}
    <tr>
{for $m=0 to 1}
{assign var=index value=$n+$m*10}
     <td class="level"><a href="javascript:;" id="refine{$index}">+{$index}</a></td>
     <td class="modifier">{foreach from=$i.refine[$index] item=p}
+{$p.value} {$p.name}<br/>
{/foreach}</td>
{/for}
    <tr>
{/for}
   </tbody>
  </table>
  <br/>
{if is_array($i.drop)}
  Drops from:
  <ul>
{foreach from=$i.drop item=d}
   <li>{$d.name} {$d.rate|string_format:"%.2f"}%</li>
{/foreach}
{/if}
  </ul>
 </div>
</div>
