<!-- Create digest Modal -->
<div id="format_digest" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Автоматическое форматирование дайджестов</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 10px">
                <div class="row">
                    <div class="offset-sm-1 col-sm-10" style="border: solid 1px rgb(0, 90, 63); border-radius: 10px; background-color: rgba(143, 219, 211, 0.562); box-shadow: 0px 5px 5px gray;">
                        <h4 style="text-align: center"> <b>Заметки для пользования</b> </h4>
                        <ul>
                            <li>Первые три параграфа дайджеста должны быть следующего характера:<br><b>"Информация"</b>, <b>&lt;тема дайджеста&gt;</b>, <b>"(материал ЦЭИР)"</b></li>
                            <li>Все картинки и таблицы (не включая текст) исчезнут из документа, потому что скачанный файл это новый Word документ сгенерированный на основе загруженного.</li>
                        </ul>

                    </div>
                </div>
                <br>
                <form method="POST" action="{{ route('upload.test') }}" id="createDigest" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="offset-sm-1 col-sm-10">
                            <div class="form-group">
                                <label>Файл ( Word, Макс: 5 MB )</label>
                                <input class="form-control" type="file" name="file">
                            </div>
                        </div>
                    </div>
                    <div class="submit-section" style="margin: 0">
                        <button class="btn btn-primary submit-btn">Загрузить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Create digest Modal -->
