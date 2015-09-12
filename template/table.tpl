<div id="tab-popup-overlay" class="popup-overlay"></div>
<div id="tab-popup" class="popup"></div>
<div class="list-opts">
{if $tab.searchbox}
 <span class="left"><input type="text" id="search"></input></span>
{/if}
 <span id="table-info">Loading...</span>
</div>
<table id="itemtable" class="list">
 <thead>
   {foreach name=col from=$tab.columns item=i}
    {if $smarty.foreach.col.index == 0}
     <td id="identifier" colspan="2"><a id="header{$smarty.foreach.col.index}" href="javascript:;">{$i}</a></td>
    {else} 
     <td><a id="header{$smarty.foreach.col.index}" href="javascript:;">{$i}</a></td>
    {/if}
   {/foreach}
 </thead>
 <tbody>
 </tbody>
</table>
<script type="text/javascript">
 page = 1
 sort = {$tab.defaultsort}
 order = {$tab.defaultorder}
 limit = 0
 cur_filter = [ ]
{if $tab.searchbox}
 cur_search = ""
{/if}
 function update_info(id) {
   $('#tab-popup .level a').click(function() {
     refinement = $(this).prop('id').substr(6);
     reload_info('refinement=' + refinement);
   });
 }

 function reload_tab() {
   // Take all filter-items together and pass them to the script
   cur_filter = []
   $('.filter input').each(function() {
     if ($(this).prop('checked')) {
       cur_filter.push($(this).prop('id').substr(6) /* remove filter-prefix */);
     }
   } );

   // Submit the load request
   $.ajax( {
    url: '{$tab.url}',
    data: 'page=' + page + '&sort=' + sort + '&order=' + order + '&filter=' + cur_filter + '&limit=' + limit{if $tab.searchbox} + '&search=' + cur_search{/if},
    success: function(r) {
      // Move all content from the table body to the table here
      $('#itemtable tbody').html($('#table', r).html());
{if isset($tab.hoverurl)}
      // Hook up hover events to all images and identifiers
      $('#itemtable tbody tr td.id').each(function() {
        $(this).append('<div class="floating-info loading"/>');
        $(this).find('a').hover(function() {
          var d = $(this).parent().find('.floating-info');
          d.show();
          // If the info hasn't been loaded, do so now
          if (d.hasClass('loading')) {
            d.removeClass('loading');
            guid = d.closest('tr').prop('id').substr(4);
            d.load('{$tab.hoverurl}?guid=' + guid);
          }
        }, function() {
          $(this).parent().find('.floating-info').hide();
        } );
      } );
{/if}
{if isset($tab.hoverurl2)}
      $('#itemtable tbody tr td.hover').each(function() {
        $(this).append('<div class="floating-info loading"/>');
        $(this).find('a').hover(function() {
          var d = $(this).parent().find('.floating-info');
          d.show();
          // If the info hasn't been loaded, do so now
          if (d.hasClass('loading')) {
            d.removeClass('loading');
            guid = d.closest('td').prop('id').substr(4);
            d.load('{$tab.hoverurl2}?guid=' + guid);
          }
        }, function() {
          $(this).parent().find('.floating-info').hide();
        } );
      } );
{/if}
{if isset($tab.linkurl)}
      // Hook click events
      $('#itemtable tbody tr td.id a').each(function() {
        var id = $(this).prop('id').substr(4);
        $(this).click(function() {
          $.ajax({
            url: '{$tab.linkurl}',
            data: 'guid=' + id,
            success: function(r) {
              $('#tab-popup').html($('#content', r).html());
              update_info();
              popup_show();
            }
          });
        });
      });
{/if}
      // Copy the table information
      $('#table-info').html($('#info', r).html());
      $('#table-info #prevpage').click(function() {
        page = page - 1
        reload_tab()
      } );
      $('#table-info #nextpage').click(function() {
        page = page + 1
        reload_tab()
      } );
    }
   } );
 }
{if isset($tab.filterurl)}
 function reload_filter() {
   $.ajax( {
     url: '{$tab.filterurl}',
     data: 'limit=' + limit,
     success: function(r) {
       $('.filter').html(r);
       $('.filter input').change(function() {
         reload_tab();
       })
       $.each(cur_filter, function(i, v) {
         $('#filter' + v).prop('checked', true);
       });
     }
   });
 }
{/if}
 function sort_on(n) {
  var sorting = [ "up", "dn" ];
  cur_style = 'sort_' + sorting[order];
  if (sort == n) {
    // Same column; need to toggle sorting order
    order = order ^ 1
    new_style = 'sort_' + sorting[order]
    var a = $('#itemtable #header' + n);
    a.removeClass(cur_style)
    a.addClass(new_style)
  } else {
    // Default to ascending when selecting a new column
    $('#itemtable #header' + sort).removeClass(cur_style);
    order = 0
    $('#itemtable #header' + n).addClass('sort_' + sorting[order]);
    sort = n
  }
  reload_tab();
 }
 $.each(new Array({count($tab.columns)}), function(i, v) {
   $('#itemtable #header' + i).click(function() {
     sort_on(i);
   } );
 });
 $('.limit a').click(function() {
   $('#limit' + limit).removeClass('selected');
   limit = $(this).prop('id').substr(5);
   $(this).addClass('selected');
{if $tab.searchbox}
   cur_search = '';
   $('#search').val('');
{/if}
{if isset($tab.filterurl)}
   reload_filter();
{/if}
   reload_tab();
 });

{if $tab.searchbox}
 // Search box
 $('#search').keyup(function() {
   typewatch(function() {
     if (cur_search == $('#search').val()) {
        return; // no change
     }
     cur_search = $('#search').val();
     reload_tab();
   }, 500);
 });
{/if}

 function reload_info(extra) {
   var guid = $('#tab-popup .guid').prop('id').substr(4);
   $.ajax({
     url: '{$tab.linkurl}',
     data: 'guid=' + guid + '&' + extra,
     success: function(r) {
       $('#tab-popup').html($('#content', r).html());
       update_info();
     }
   });
 }

 // Handle closing the popup if we need
 $('#tab-popup').click(function() {
   $('html').addClass('overlay');
   $('#tab-popup-overlay').addClass('popup-overlay-visible');
   $('#tab-popup').addClass('popup-visible');
 });
 $('#tab-popup-overlay').click(function() {
   $(this).removeClass('popup-overlay-visible');
   $('#tab-popup').removeClass('popup-visible');
 });


 // Handle initial selection
 $('#limit' + limit).addClass('selected');
 sort_on(sort)
{if isset($tab.filterurl)}
 reload_filter();
{/if}
 reload_tab();
</script>
