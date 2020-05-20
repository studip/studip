const Plus = {
    setModule: function () {
        $.ajax({
            "url": STUDIP.URLHelper.getURL("dispatch.php/course/plus/trigger"),
            "data": {
                "moduleclass": $(this).data("moduleclass"),
                "key": $(this).data("key"),
                "active": $(this).is(":checked") ? 1 : 0
            },
            "dataType": "json",
            "type": "post",
            "success": function (output) {
                $(".tabs_wrapper").replaceWith(output.tabs);
                //hallo
            }
        });
    }
};



export default Plus;
