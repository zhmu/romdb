<div class="item">
 <span class="q{$i.rare}">{$i.name}</span>
 <div class="line">
  <span class="left">Use additional card abilities</span>
 </div>
 {foreach from=$i.attrs item=a}
 <div class="ghoststat">+{$a.value} {$a.name}</div>
 {/foreach}
</div>
