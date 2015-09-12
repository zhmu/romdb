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
  <span class="left">Physical Damage {$i.pdmg}</span>
  <span class="right">Attack speed {$i.attackspeed}</span>
 </div>
 {if isset($i.mdmg)}
 <div class="line">
  <span class="left">Magical Damage {$i.mdmg}</span>
 </div>
 {/if}
 {foreach from=$i.attrs item=a}
 <div class="ghoststat">+{$a.value} {$a.name}</div>
 {/foreach}
</div>
