$(document).ready(function () {
    $("#menu-close").click(function (e) {
        e.preventDefault();
        $("#sidebar-wrapper").toggleClass("active");
    });

// Opens the sidebar menu
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#sidebar-wrapper").toggleClass("active");
    });

    $(".add_to_group").wrapInner(function () {
        return "<a href='tg://resolve?domain=" + $(this).text() + "&startgroup=0'></div>";
    });
    $(".wrote").wrapInner(function () {
        return "<a href='tg://resolve?domain=" + $(this).text() + "'></div>";
    });
    $(".tg_user a").prepend("@");

    $('#searchline').keyup(function () {
        if ($(this).val().length > 0) {
            $("#suggestions").show();
            var type;
            switch ($("#search_btn").val()) {
                case "0":
                    type = "user";
                    break;
                case "1":
                    type = "group";
                    break;
            }
            $.ajax({
                    method: "POST",
                    url: "logic.php",
                    data: {please: type + "list", query: $(this).val()}
                })
                .done(function (msg) {
                    $("#suggestions").empty();
                    var numbers = msg;
                    numbers = JSON.parse(numbers);
                    numbers.forEach(function (item) {
                        var text;
                        switch (type) {
                            case "user":
                                text = item[2] + " " + item[3];
                                if (item[1].length > 0)text += " <b>(@" + item[1] + ")</b>";
                                break;
                            case "group":
                                text = item[1];
                                break;
                        }
                        $("#suggestions").append("<div class='suggest load_" + type + "' onclick='load_" + type + "(" + item[0] + ")'>" + text + "</div>");
                    });
                });
        } else {
            $("#suggestions").hide();
        }
    });

    $(".dropdown-menu li").click(function (e) {
        var searchBtn = $("#search_btn");
        searchBtn.val($(this).index());
        searchBtn.text($(this).text());
        return false;
    });

    $('.reward').tooltip();
});

function load_group(id) {
    $("#suggestions").hide();
    $.ajax({
        url: './group_view.php?group_id=' + id,
        complete: function (response) {
            $('#content').html(response.responseText);
        }
    });
    return false;
}

function load_user(id) {
    $("#suggestions").hide();
    $.ajax({
        url: './user_view.php?user_id=' + id,
        complete: function (response) {
            $('#content').html(response.responseText);
        }
    });
    return false;
}
