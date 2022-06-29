@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
    <div class="page-header">
        <div class="row">
            <div class="col-sm-12">
                <ul class="nav nav-tabs nav-tabs-bottom">
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == '/') ? 'active' : '' }}" href="{{ route('home') }}">Недвижимости</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'ordered') ? 'active' : '' }}" href="{{ route('ordered') }}">Автомобили</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'helping') ? 'active' : '' }}" href="{{ route('helping') }}">Работа</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'helping') ? 'active' : '' }}" href="{{ route('helping') }}">Продукты</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
	<!-- Page Content -->
	<div class="content container-fluid">
        <!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Filter -->
		<div class="row filter-row">
            <div class="col-sm-4 col-md-2">
                <label for="input">Источник</label>
                <div class="form-group">
                    <select class="form-control" name="source">
                        <option value="olx">Olx</option>
                        <option value="uybor">Uybor</option>
                    </select>
                </div>
            </div>

            <div class="col-sm-4 col-md-2">
                <label for="input">Тип</label>
                <div class="form-group">
                    <select class="form-control" name="source">
                        <option value="olx">Продажа</option>
                        <option value="uybor">Аренда</option>
                    </select>
                </div>
            </div>

            <div class="col-sm-4 col-md-2">
                <label for="input">От</label>
                <div class="form-group cal-icon">
                    <input class="form-control datetimepicker" id="startDate" name="startDate" wire:model="startDate" >
                </div>
            </div>
            <div class="col-sm-4 col-md-2">
                <label>До</label>
                <div class="form-group cal-icon">
                    <input class="form-control datetimepicker" id="endDate" name="endDate" name="endDate" wire:model="endDate">
                </div>
            </div>
            <div class="col-sm-4 col-md-2" style="float: right">
                <label style="color: #f7f7f7">S</label>
                <div class="">
                    <a href="#" class="btn btn-primary search_button">Скачать</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive" id="employeeTable">
                    <table class="table custom-table" id="filteredTasks" style="overflow-y: auto; height: 110px;">
                        <thead id="employee_header">
                            <tr>
                                <th>Названия</th>
                                <th>Регион</th>
                                <th>Город</th>
                                <th>Цена:</th>
                                <th>Кол. комнат</th>
                                <th>Площадь</th>
                                <th>Cостояние</th>
                                <th>Этажность</th>
                                <th>Этаж</th>
                            </tr>
                        </thead>
                        <tbody style="overflow: auto;">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
	</div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        function fnExcelReport()
        {
            var tab_text="<table border='2px'><tr>";
            var textRange; var j=0;
            tab = document.getElementById('filteredTasks'); // id of table

            for(j = 0 ; j < tab.rows.length ; j++)
            {
                tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
                //tab_text=tab_text+"</tr>";
            }

            tab_text=tab_text+"</table>";
            tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
            tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
            tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
            {
                txtArea1.document.open("txt/html","replace");
                txtArea1.document.write(tab_text);
                txtArea1.document.close();
                txtArea1.focus();
                sa=txtArea1.document.execCommand("SaveAs",true,"Report.xlsx");
            }
            else                 //other browser not tested on IE 11
                sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));

            return (sa);
        }
    </script>
@livewireScripts
@endsection
