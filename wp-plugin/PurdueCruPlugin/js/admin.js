function($) {

    function createOptions(successCallback) {
        var options = {
            url:        ajaxurl, // ajaxurl defined by Wordpress internally for us
            dataType:   json,
            success:    successCallback
        }
    }

    function getActionDiv() {
        if ($('div#action-message').length == 0) {
            $('div.wrap + h2').after("<div id=\"action-message\"><p></p></div>");
        }
        return $('div#action-message')
    }

    function setActionMessage(message) {
        getActionDiv().children("p").html(message);
    }

    function handlePluginAction(data) {
        if (data.success == "1") {
            if (typeof data.message != 'undefined') {
                message = "Success";
            } else {
                message = data.message;
            }
            setActionMessage(message);
            getActionDiv.removeClass("error").addClass("updated");
        } else {
            if (typeof data.message != 'undefined') {
                message = "Failure";
            } else {
                message = data.message;
            }
            setActionMessage(message);
            getActionDiv.removeClass("updated").addClass("error");
        }
    }
}
