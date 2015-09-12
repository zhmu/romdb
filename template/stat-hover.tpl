<div class="item">
 <span class="q{$i.rare}">{$i.name}</span>
 {foreach from=$i.attrs item=a}
 <div class="ghoststat">+{$a.value} {$a.name}</div>
 {/foreach}
</div>
