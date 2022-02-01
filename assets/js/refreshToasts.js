module.exports = function() {
    console.log("refreshing toasts");
    $("#toasts").load("/toasts");
}