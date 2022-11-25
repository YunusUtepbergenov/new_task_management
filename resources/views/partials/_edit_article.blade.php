<!-- Edit Article Modal -->
<div id="edit_article" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Изменить статью</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('article.update') }}" id="editArticle">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите названия</label>
                                <input class="form-control" name="name" id="article_name1" type="text">
                                <input type="hidden" name="id" id="article_id">
                            </div>
                            {{-- <div class="alert alert-danger" id="user_name"></div> --}}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Краткое Содержание</label>
                                <textarea rows="4" class="form-control" name="description" id="article_description1" placeholder="Поручение / Комментария"></textarea>
                            </div>
                            {{-- <div class="alert alert-danger" id="description"></div> --}}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Тематика</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="category_id" id="article_category_id1">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id}}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Автор</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="user_id" id="article_user_id1">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Cсылка</label>
                                <input class="form-control" name="link" type="text" id="article_link1">
                            </div>
                            {{-- <div class="alert alert-danger" id="email"></div> --}}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Файл ( Макс: 5 MB )</label>
                                <input class="form-control" type="file" name="file" id="article_file1">
                            </div>
                            {{-- <div class="alert alert-danger" id="file"></div> --}}
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
<!-- /Edit Article Modal -->
