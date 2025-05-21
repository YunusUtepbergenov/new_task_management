@extends('layouts.main')

@section('styles')
  <style>
      body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', sans-serif;
      }
      .card {
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
      }
      .card-header-custom {
        color: #fff;
        padding: 1rem;
        text-align: center;
      }
      .workload-title{
        font-size: 0.85rem;
        font-weight: 600;
        color: white;
      }
      .card-body-custom {
        background-color: #fff;
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
      }
      .scrollable-task-table {
        overflow-y: auto;
        max-height: 150px;
      }
      .card-title {
        font-weight: 600;
      }
      .section-title {
        font-size: 1rem;
        font-weight: bold;
        margin: 20px 0 10px;
      }
      .workload-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
      }
      .progress-ring {
        position: relative;
        width: 40px;
        height: 40px;
      }
      .progress-ring circle {
        fill: none;
        stroke-width: 5;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
      }
      .progress-ring-text {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
      }
      .task-table {
        width: 100%;
        font-size: 0.7rem;
      }
      .task-table th, .task-table td {
        padding: 4px 8px;
      }
      .task-count {
        font-weight: bold;
        font-size: 1.7rem;
        line-height: 1;
      }
      .task-label {
        display: block;
        font-size: 0.9rem;
        line-height: 1;
      }
      .workload-section {
        display: flex;
        justify-content: center;
        gap: 50%;
        align-items: center;
      }
  </style>
@endsection

@section('main')
  @foreach ($sectors as $sector)
    <div class="section-title">{{$sector->name}}</div>
    <hr>
    <div class="row g-3 mb-4">
      @foreach ($sector->users->sortBy('role_id') as $user)
        @php
            $taskCount = $user->tasks->count();

            if ($taskCount > 5) {
                $headerColor = '#e63946';
                $workloadStatus = 'High';
                $offset = 25;
            } elseif ($taskCount >= 3) {
                $headerColor = '#f77f00';
                $workloadStatus = 'Medium';
                $offset = 55;
            } else {
                $headerColor = '#2a9d8f';
                $workloadStatus = 'Low';
                $offset = 85;
            }
        @endphp
        <div class="col-md-3">
          <div class="card h-100">
              <div class="card-header-custom" style="background-color: {{$headerColor}};">
                {{-- <img src="https://ijro.cerr.uz/user_image/avatar.jpg" class="workload-avatar" alt="{{$user->name}}"> --}}
                <h5 class="workload-title">{{$user->name}}</h5>
              </div>
              <div class="card-body-custom">
                <div class="workload-section mb-3">
                  <div>
                    <div class="task-count">{{$taskCount}}</div>
                    <span class="task-label">Задачи</span>
                  </div>
                  <div class="text-center">
                    <div class="progress-ring">
                      <svg width="40" height="40">
                        <circle r="18" cx="20" cy="20" stroke="#e0e0e0"></circle>
                        <circle r="18" cx="20" cy="20" stroke="{{$headerColor}}" stroke-dasharray="113.097" stroke-dashoffset="{{$offset}}"></circle>
                      </svg>
                    </div>
                    <div class="small mt-1" style="color: {{$headerColor}}">{{$workloadStatus}}</div>
                  </div>
                </div>
                <div class="scrollable-task-table">
                  <table class="table table-bordered task-table">
                    <thead>
                      <tr class="table-light">
                        <th>#</th>
                        <th>Название</th>
                        <th>Срок</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($user->tasks as $key=>$task)
                        <tr><td>{{$key + 1}}</td><td>{{$task->name}}</td><td>{{$task->deadline}}</td></tr>                      
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
        </div>
      @endforeach    
    </div>
  @endforeach

@endsection