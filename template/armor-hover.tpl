<div class="item">
 <span class="q{$i.rare}">{$i.name}</span>
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
 {foreach from=$i.attrs item=a}
 <div class="ghoststat">+{$a.value} {$a.name}</div>
 {/foreach}
</div>
