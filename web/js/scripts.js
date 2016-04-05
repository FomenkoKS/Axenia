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

    $('input.typeahead').typeahead({
        name: 'users',
        prefetch: 'data/users.json',
        limit: 10,
        templates: {
            notfound: [
                '<div class="empty-message">',
                'Пользователь не найден',
                '</div>'
            ].join('\n')
        }
    });

    $('.typeahead').bind('typeahead:select', function(ev, suggestion) {
        alert('Selection: ' + suggestion);
    });

    $(".dropdown-menu li a").click(
        function (e) {
            e.preventDefault();
            $("#search").text($(this).text());
            $("#search").val($(this).parent().index());
            var type;

            type = ($("#search").val() == "0") ? "users" : "groups";
            $('.twitter-typeahead').remove();
            $("#search-button").after("<input type=\"text\" class=\"form-control typeahead\">");
            $('input.typeahead').typeahead({
                name: type,
                prefetch: 'data/' + type + '.json',
                limit: 10
            });

        }
    );

    $('.typeahead').bind('typeahead:select', function (ev, suggestion) {
        alert('Selection: ' + suggestion);
    });
});

function load_group(id) {
    $.ajax({
        url: './group_view.php?group_id=' + id,
        complete: function (response) {
            $('#content').hide();
            $('#content').html(response.responseText);
            $('#content').show("slow");
        }
    });
    return false;
}

function load_user(id) {
    $.ajax({
        url: './user_view.php?user_id=' + id,
        complete: function (response) {
            $('#content').hide();
            $('#content').html(response.responseText);
            $('#content').show("slow");
        }
    });
    return false;
}