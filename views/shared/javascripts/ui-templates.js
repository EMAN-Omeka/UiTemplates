$ = jQuery;

$(document).ready(function(){
  $.fn.slick = function() {
    var _ = this,
      opt = arguments[0],
      args = Array.prototype.slice.call(arguments, 1),
      l = _.length,
      i,
      ret;
    for (i = 0; i < l; i++) {
      if (typeof opt == 'object' || typeof opt == 'undefined')
          _[i].slick = new Slick(_[i], opt);
      else
          ret = _[i].slick[opt].apply(_[i].slick, args);
      if (typeof ret != 'undefined') return ret;
    }
    return _;
  };
  $('.suite').click(function() {
    $(this).parent().parent().find('.fieldcontentcomplet').show();
    $(this).parent().parent().find('.fieldcontentshort').hide();
  });
  $('.replier').click(function() {
    $(this).parent().parent().find('.fieldcontentcomplet').hide();
    $(this).parent().parent().find('.fieldcontentshort').show();
  });
  $('#transcripted > span').each(function (i, e) {
    $('#itemfiles').find('div#' + e.innerHTML).addClass('transcripted');
  });
   jQuery("#files-carousel").slick({
		dots: true,
		appendDots: '#plugin_gallery > span',
		lazyLoad: 'ondemand',
    rows: 4,
    slidesPerRow: 4,
    pauseOnFocus: true,
    cssEase : 'ease',
    fade: true,
  });
});
