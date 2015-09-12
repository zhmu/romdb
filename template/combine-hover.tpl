<div class="item">
 <span class="q1">{$i.name}</span>
 <div class="line">
  <span class="left">Created from</span>
 </div>
 {foreach from=$i.sources item=s}
 <div class="line">- {$s.amount} x {$s.name}</div>
 {/foreach}
</div>
