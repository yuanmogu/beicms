(function($) {
	'use strict';

	/*---------- 01. Preloader ----------*/
	$(window).on('load', function () {
		$('.preloader').fadeOut('slow');
	});


	$(function() {

		/**
		 * 通用form - ajax提交
		 */
		$('.ajax-form').on('click', function() {
			var _form = $(this).data('form');
			var _url = $(_form).attr('action');
			var _method = $(_form).attr('method');
			var _data = $(_form).serialize();
		
			$.ajax({
				url: _url,
				type: _method,
				data: _data,
				success: function(info) {
					
					if (info.code === 1) {
						Swal.fire({
						  icon: 'success',
						  title: info.msg
						}).then((result) => {
							if (result.isConfirmed) {
								 location.reload();
							}
						})
						
					} else {
						Swal.fire({
							icon: 'error',
							title: info.msg,
							timer: 1500,
							showConfirmButton: false
						})
					}
					
				}
			});
			return false;
		});




	});
})(jQuery);
