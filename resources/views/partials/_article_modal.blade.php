<!-- Create Article Modal -->
<div id="create_article" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новая статья</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('articles.store') }}" id="createArticle" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите названия</label>
                                <input class="form-control" name="name" type="text" placeholder="Введите названия">
                            </div>
                            <div class="alert alert-danger" id="article_name"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Краткое Содержание</label>
                                <textarea rows="4" class="form-control" name="description" placeholder="Краткое Содержание"></textarea>
                            </div>
                            <div class="alert alert-danger" id="article_description"></div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Тематика</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="category_id">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id}}">{{ $category->name }}</option>
                                @endforeach
                            </select>
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
                            <div class="alert alert-danger" id="article_file"></div>
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
