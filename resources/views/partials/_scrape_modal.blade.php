<!-- Create Article Modal -->
<div id="create_scrape" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить Файл</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('scrape.upload') }}" id="createScrape" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите названия</label>
                                <input class="form-control" name="name" type="text" placeholder="Введите названия">
                            </div>
                            {{-- <div class="alert alert-danger" id="article_name"></div> --}}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Категория</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="category">
                                <option value="houses">Недвижимости</option>
                                <option value="jobs">Работы</option>
                                <option value="cars">Автомобили</option>
                                <option value="products">Продукты</option>
                                <option value="corruption">Опросник по коррупции</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Дата</label>
                        <div class="col-sm-4">
                            <input class="form-control datetimepicker" name="date">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Файл ( Pdf/Word, Макс: 10 MB )</label>
                                <input class="form-control" type="file" name="file">
                            </div>
                            {{-- <div class="alert alert-danger" id="article_file"></div> --}}
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
<!-- /Create Article Modal -->
