@extends('layouts.main')
@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
                    <h3 class="page-title">Автоматическое форматирование дайджестов</h3>
				</div>
            </div>
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item">
                </li>
            </ul>
		</div>
		<!-- /Page Header -->

        <div class="row">
            <div class="offset-sm-1 col-sm-10" style="margin:auto; border: solid 1px rgb(0, 90, 63); border-radius: 10px; background-color: rgba(143, 219, 211, 0.562); box-shadow: 0px 5px 5px gray;">
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
            <div class="form-row">
                <div class="offset-sm-2 col-sm-6">
                    <input type="file" class="form-control" name="file">
                </div>
                <div class="col-sm-2">
                    <button type="text" class="btn btn-primary">Форматировать</button>
                </div>
            </div>
        </form>
        <hr><br>
        @livewire('documents.digest-words')
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    <script>
        window.addEventListener('existing-word', event => {
            alert('Это слово уже добавлено');
        })
    </script>
@endsection
