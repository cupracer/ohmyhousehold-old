$(document).ready(function () {
    $(document).on("click", "#checkout_button", function(){
        let button = $(this);
        let checkoutLink = button.val();
        button.prop('disabled', true);

        $.ajax({
            url: checkoutLink,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                if(data.status === "success") {
                    datatable.draw();
                }else if(data.status === "error") {
                }
            }
        });
    });
});
