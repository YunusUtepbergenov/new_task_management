@extends('layouts.main')

@section('styles')
	<link rel="stylesheet" href="{{ asset('css/ijaboCropTool.min.css') }}">
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
        <div class="row">
            {{-- <div class="offset-lg-1 col-md-3 settings_page">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Настройки</h4>
                    </div>

                    <div class="card-body">
                        <div class="account-settings">
                            <div class="user-profile">
                                <div class="user-avatar">
                                    <img src="{{ (Auth::user()->avatar) ? asset('user_image/'.Auth::user()->avatar) : asset('user_image/avatar.jpg') }}" class="user_image">
                                </div>
                                <input type="file" name="avatar_img" id="avatar_img" style="opacity: 0; height:1px; display:none">
                                <a href="javascript:void(0)" class="btn btn-primary" id="change_picture_btn">Cменить картинку</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="offset-md-1 col-md-10 settings_page">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Профиль</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="account-settings">
                                    <div class="user-profile">
                                        <div class="user-avatar">
                                            <img src="{{ (Auth::user()->avatar) ? asset('user_image/'.Auth::user()->avatar) : asset('user_image/avatar.jpg') }}" class="user_image">
                                        </div>
                                        <input type="file" name="avatar_img" id="avatar_img" style="opacity: 0; height:1px; display:none">
                                        <a href="javascript:void(0)" class="btn btn-primary" id="change_picture_btn">Cменить картинку</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <form action="{{ route('update.password') }}" method="POST">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Прежний пароль</label>
                                        <div class="col-lg-9">
                                            @error('old_password')
                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                            @enderror
                                            <input type="password" class="form-control" name="old_password" value="{{ old('old_password') }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Новый пароль</label>
                                        <div class="col-lg-9">
                                            @error('new_password')
                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                            @enderror
                                            <input type="password" class="form-control" name="new_password" value="{{ old('new_password') }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label">Подтвердите пароль</label>
                                        <div class="col-lg-9">
                                            @error('confirm_password')
                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                            @enderror
                                            <input type="password" class="form-control" name="confirm_password" value="{{ old('confirm_password') }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Изменить</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('js/libs/ijaboCropTool.min.js') }}"></script>
    <script>
        $(document).on('click', '#change_picture_btn', function(){
            $('#avatar_img').click();
        });

        $('#avatar_img').ijaboCropTool({
          preview : '.user_image',
          setRatio:1,
          allowedExtensions: ['jpg', 'jpeg','png'],
          buttonsText:['Сохранить','Выходить'],
          buttonsColor:['#30bf7d','#ee5155', -15],
          processUrl:'{{ route("profile.change") }}',
          withCSRF:['_token','{{ csrf_token() }}'],
          onSuccess:function(message, element, status){
            console.log(message);
            //  alert(message);
          },
          onError:function(message, element, status){
            alert(message);
          }
       });
    </script>
@endsection
