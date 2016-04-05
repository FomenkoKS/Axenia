<div class="col-lg-12 center">
    <? echo $logic->GetStats(); ?>
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
            <h3>Найти:</h3>
            <div class="input-group">
                <div class="input-group-btn" id="search-button">
                    <button type="button" class="btn btn-default" data-toggle="dropdown" id="search" value="0">Пользователя</button>
                    <ul class="dropdown-menu">
                        <li><a href="#">Пользователя</a></li>
                        <li><a href="#">Группу</a></li>
                    </ul>
                </div>
                <input type="text" class="form-control typeahead">
            </div>
        </div>
</div>