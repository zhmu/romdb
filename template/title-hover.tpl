<div class="item">
 <span class="q{$i.rare}">{$i.name}</span>
 <div class="line">
  <span class="left">Title</span>
 </div>
 {foreach from=$i.attrs item=a}
 <div class="ghoststat">+{$a.value} {$a.name}</div>
 {/foreach}
 <div class="line">
  <span class="left note">{$i.note|nl2br}</span>
 </div>
</div>
