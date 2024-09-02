(function (_, $) {
    if (window.location.href.includes('/checkout')) {
		var suggestionsVisible = false; // Флаг для отслеживания видимости подсказок
        var typingTimer;
        var doneTypingInterval = 1000; // Время в миллисекундах
        $(document).on('keyup', '.litecheckout__field.cm-field-container.litecheckout__field--xlarge.litecheckout__field--input', function() {
            clearTimeout(typingTimer); // Сброс таймера при каждом нажатии клавиши
            var $div = $(this);
            var $input = $div.find('input');
            var inputValue = $input.val();
            if (inputValue) {
                typingTimer = setTimeout(function() {
                    var url = fn_url('?dispatch=ee_dadata');
                    var params = {
                        hidden: true,
                        caching: false,
                        force_exec: true,
                        save_history: true,
                        method: 'post',
                        callback: function(data) {
                            if (data.response.dadata && data.response.dadata.error == '') {
                                // Создаем или очищаем контейнер для подсказок
                                var $suggestions = $('#ee_front_suggestions');
                                if ($suggestions.length === 0) {
                                    $suggestions = $('<div id="ee_front_suggestions"></div>').appendTo('body');
                                } else {
                                    $suggestions.empty();
                                }
                                // Добавляем подсказки
                                data.response.dadata.suggestions.forEach(function(item) {
                                    var $suggestionItem = $('<div class="ee_front_suggestions-item"></div>').text(item.unrestricted_value);
                                    $suggestions.append($suggestionItem);

                                    // Обработчик клика по подсказке
                                    $suggestionItem.on('click', function() {
                                        $input.val(item.unrestricted_value);
										$('#litecheckout_s_zipcode').val(item.data.postal_code);
                                        $suggestions.hide();
										 suggestionsVisible = false; // Подсказки больше не видны
                                    });
                                });
                                // Позиционирование контейнера для подсказок
                                var inputOffset = $input.offset();
                                $suggestions.css({
                                    top: inputOffset.top + $input.outerHeight(),
                                    left: inputOffset.left,
                                    width: $input.outerWidth()
                                }).show();
								suggestionsVisible = true; // Подсказки видны
                            }
                        },
                        data: {'query': inputValue},
                        overlay: 'window'
                    };
                    $.ceAjax('request', url, params);
                }, doneTypingInterval);
            }
        });
        // Скрытие подсказок при клике вне их области
		$(document).on('click', function(event) {
			if (suggestionsVisible 
				&& !$(event.target).closest('.litecheckout__field.cm-field-container.litecheckout__field--xlarge.litecheckout__field--input').length
				&& !$(event.target).closest('#ee_front_suggestions').length
				&& !$(event.target).closest('#login_phone_popupcp_like_auth_form').length
				&& !$(event.target).closest('#login_phone_litecheckout_login_block_inner').length) {
				$('#ee_front_suggestions').hide();
				suggestionsVisible = false; // Подсказки больше не видны
			}
		});
    }
}(Tygh, Tygh.$));
