<div class="item">
 <span class="q{$i.rare}">{$i.name}</span>
 {foreach from=$i.source item=s}
 <div class="ghoststat"><img src="image.php?guid={$s.imageid}"/>{$s.name} ({$s.count})</div>
 {/foreach}
</div>
