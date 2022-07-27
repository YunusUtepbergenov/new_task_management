<!-- Edit Digest Modal -->
<div id="edit_digest" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Изменить дайджест</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('digest.update') }}" id="editDigest">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите названия</label>
                                <input class="form-control" name="name" id="digest_name1" type="text">
                                <input type="hidden" name="id" id="digest_id">
                            </div>
                            {{-- <div class="alert alert-danger" id="user_name"></div> --}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Источник</label>
                                <input type="file" class="form-control" name="paper" id="digest_paper1">
                            </div>
                            {{-- <div class="alert alert-danger" id="digest_description1"></div> --}}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Cсылка источника</label>
                                <input class="form-control" name="link" type="text" id="digest_link1">
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
