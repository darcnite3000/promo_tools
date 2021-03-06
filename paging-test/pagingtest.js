var numPages = 144;

function pagination(active){
  if(active == undefined) active = 0;
  var pagination = [], temp = "", visible = 10;
  var leftPages = 0, rightPages = 0;
  if(numPages > visible){
    if(active < visible/2){
      leftPages = active;
      rightPages = visible - leftPages;
    }else if(active > numPages-(visible/2)){
      rightPages = numPages - active;
      leftPages = visible - rightPages;
    }else{
      leftPages = visible/2
      rightPages = visible/2
    }
  }

  if(active!=0){
    temp+= '<li class="pager-first"><a href="#" title="Go to first page" class="feed-page" data-pagenum="'+(0)+'">&laquo; first</a></li>';
    temp+= '<li class="pager-previous"><a href="#" title="Go to previous page" class="feed-page" data-pagenum="'+(active-1)+'">&lsaquo; previous</a></li>';
  }
  for (var i = 0; i < numPages; i++) {
    if(i == 0 || i == numPages-1 || (i > active-leftPages && i < active+rightPages)){
      if(rightPages+active<numPages && i == numPages-1){
        temp+= '<li class="pager-ellipsis">&hellip;</li>';
      }
      if(active == i){
      temp+= '<li class="pager-current">'+(i+1)+' </li>';
      }else{
      temp+= '<li class="pager-item"><a href="#" title="Go to page '+(i+1)+'" class="feed-page" data-pagenum="'+i+'">'+(i+1)+'</a></li>';
      }
      if(leftPages-active>0 && i == 0){
        temp+= '<li class="pager-ellipsis">&hellip;</li>';
      }
    }
  };
  if(active!=(numPages-1)){
    temp+= '<li class="pager-next"><a href="#" title="Go to next page" class="feed-page" data-pagenum="'+(active+1)+'">next &rsaquo;</a></li>';
    temp+= '<li class="pager-last"><a href="#" title="Go to last page" class="feed-page" data-pagenum="'+(numPages-1)+'">last &raquo;</a></li>';
  }
  return $('<div class="item-list item-list-pager"><ul class="pager">'+temp+'</ul></div>');
}

module.exports = pagination;