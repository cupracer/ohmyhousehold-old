import refreshToasts from "../../refreshToasts";

$(document).on('click', '.checkbox_transaction_state', function(){
    let checkbox = $(this);
    let checkedState = checkbox.prop('checked');
    let jsonUrl = checkbox.data('jsonUrl');

    checkbox.attr("disabled", true);

    $.ajax({
        url: jsonUrl,
        type: 'post',
        data: {
            state: checkedState,
        },
        success: function(data, textStatus, jqXHR) {
            console.log(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
            console.log(errorThrown);
            checkbox.prop('checked', !checkedState);
        },
        complete: function(jqXHR, textStatus) {
            refreshToasts();
            checkbox.removeAttr("disabled");
        }
    });
});