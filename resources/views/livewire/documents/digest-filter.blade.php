<div>
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Дайджесты</h3>
            </div>
            <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#format_digest">Форматирование дайджеста</a>
            </div>
            <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_digest">Добавить дайджест</a>
            </div>
        </div>
        <ul class="nav nav-tabs nav-tabs-bottom">
            <li class="nav-item">
            </li>
        </ul>
    </div>
    <!-- /Page Header -->
    <div class="form-group">
        <input type="text" wire:model='search' class="form-control" placeholder="Поиск">
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="employeeTable">
                <table class="table custom-table article-table" id="myTable" style="overflow-y: auto;">
                    <thead id="employee">
                        <tr>
                            <th class="skip-filter"><span>&#8470;</span></th>
                            <th class="skip-filter"></th>
                            <th class="skip-filter">Название</th>
                            <th>Автор</th>
                            <th>Отдел</th>
                            <th class="skip-filter">Источник</th>
                            <th class="skip-filter">Дата</th>
                            <th class="skip-filter">Cсылка</th>
                            <th class="skip-filter">Файл</th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @php
                            $key = ($digests->currentpage() - 1) * $digests->perpage()
                        @endphp

                        @foreach ($digests as $article)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    @if ($article->user_id == auth()->user()->id)
                                        <div class="dropdown dropdown-action profile-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                    <form action="{{ route('digests.destroy', $article->id) }}" method="POST">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <button class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>Удалить</button>
                                                    </form>
                                                    <a href="#" onclick="editDigest({{ $article->id }})" class="dropdown-item" data-toggle="modal" data-target="#edit_digest"><i class="fa fa-trash-o m-r-5"></i>Изменить</a>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                        <a href="#">{{ $article->name }}</a>
                                </td>
                                <td>{{ $article->user->name }}</td>
                                <td>{{ $article->user->sector->name }}</td>
                                <td>
                                    @if ($article->paper)
                                        <a href="{{ route('paper.download', $article->paper) }}">{{ substr($article->paper, 13, 40) }}</a></td>
                                    @else

                                    @endif
                                <td>{{ $article->created_at->format('Y-m-d') }}</td>
                                <td style="text-align: center">
                                    @if ($article->link)
                                        <?php
                                            $article_str = explode(";", $article->link);
                                        ?>

                                        @for ($i = 0; $i < count(explode(";", $article->link)); $i++ )
                                            <a href="{{ $article_str[$i] }}" target="_blank">{{ parse_url(trim($article_str[$i]), PHP_URL_HOST) }}</a><br>
                                        @endfor
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('digest.download', $article->file) }}">
                                        @if (pathinfo($article->file, PATHINFO_EXTENSION) == "pdf")
                                            <span class="badge bg-inverse-danger">Pdf</span>
                                        @elseif (pathinfo($article->file, PATHINFO_EXTENSION) == "doc" || pathinfo($article->file, PATHINFO_EXTENSION) == "docx")
                                            <span class="badge bg-inverse-primary">Doc</span>
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{ $digests->links() }}
</div>
