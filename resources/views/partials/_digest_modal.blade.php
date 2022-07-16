<!-- Create digest Modal -->
<div id="create_digest" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новый дайджест</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('digests.store') }}" id="createDigest" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите названия</label>
                                <input class="form-control" name="name" type="text" placeholder="Введите названия">
                            </div>
                            <div class="alert alert-danger" id="digest_name"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Журнал</label>
                                <input class="form-control" name="paper" placeholder="Введите названия журнала">
                            </div>
                            <div class="alert alert-danger" id="digest_description"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Cсылка</label>
                                <input class="form-control" name="link" type="text" placeholder="Cсылка">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Файл ( Pdf/Word, Макс: 5 MB )</label>
                                <input class="form-control" type="file" name="file">
                            </div>
                            <div class="alert alert-danger" id="digest_file"></div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Create digest Modal -->
