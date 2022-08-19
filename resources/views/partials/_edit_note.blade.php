<!-- Edit Digest Modal -->
<div id="edit_note" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Изменить записку</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('note.update') }}" id="editNote">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите названия</label>
                                <input class="form-control" name="name" id="note_name1" type="text">
                                <input type="hidden" name="id" id="note_id1">
                            </div>
                            <div class="alert alert-danger" id="note_name2"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Источник</label>
                                <input type="file" class="form-control" name="paper" id="note_paper1">
                            </div>
                            <div class="alert alert-danger" id="note_paper2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Cсылка источника</label>
                                <input class="form-control" name="link" type="text" id="note_link1">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Файл ( Pdf/Word, Макс: 5 MB )</label>
                                <input class="form-control" type="file" name="file" id="digest_file1">
                            </div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Edit Digest Modal -->
