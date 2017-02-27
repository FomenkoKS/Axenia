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
                    url: "jsonData.php",
                    data: {please: type + "list", query: $(this).val()}
                })
                .done(function (msg) {
                    $("#suggestions").empty();
                    var numbers = msg;
                    numbers = JSON.parse(numbers);
                    numbers.forEach(function (item) {
                        var text;
                        var div;
                        switch (type) {
                            case "user":
                                text = item[1] + " " + item[2];
                                if (item[1].length > 0) {
                                    text += " <b>(@" + item[3] + ")</b>"
                                }
                                div = "<div class='suggest load_user' onclick='load_user(" + item[0] + ", \""+item[3]+"\")'>" + text + "</div>";
                                break;
                            case "group":
                                text = item[1];
                                div = "<div class='suggest load_group' onclick='load_group(" + item[0] + ", \""+item[1]+"\")'>" + text + "</div>";
                                break;
                        }
                        $("#suggestions").append(div);
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
        $('#searchline').focus();
        $('#search-button').removeClass('open');
        return false;
    });

    $('.reward').tooltip();
});

function load_group(id, text) {
    $("#suggestions").hide();
    $('#searchline').val(text);
    $.ajax({
        url: './group_view.php?group_id=' + id,
        complete: function (response) {
            $('#content').html(response.responseText);
        }
    });
    return false;
}

function load_user(id, text) {
    $("#suggestions").hide();
    $('#searchline').val(text);
    $.ajax({
        url: './user_view.php?user_id=' + id,
        complete: function (response) {
            $('#content').html(response.responseText);
        }
    });
    return false;
}
