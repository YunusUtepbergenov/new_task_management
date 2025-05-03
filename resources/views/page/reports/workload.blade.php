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
            font-size: 1.1rem;
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
            font-size: 1.5rem;
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
            font-size: 0.9rem;
          }
          .task-table th, .task-table td {
            padding: 4px 8px;
          }
          .task-count {
            font-weight: bold;
            font-size: 2.5rem;
            line-height: 1;
          }
          .task-label {
            display: block;
            font-size: 1.1rem;
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
<div class="section-title">Сектор по изучению согласованности параметров макроэкономической политики и прогнозирования</div>
<hr>
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card h-100">
        <div class="card-header-custom" style="background-color: #e63946;">
          <img src="https://ijro.cerr.uz/user_image/avatar.jpg" class="workload-avatar" alt="Насриддинов Фазлиддин Файзуллаевич">
          <h5 class="workload-title">Насриддинов Фазлиддин</h5>
        </div>
        <div class="card-body-custom">
          <div class="workload-section mb-3">
            <div>
              <div class="task-count">8</div>
              <span class="task-label">Задачи</span>
            </div>
            <div class="text-center">
              <div class="progress-ring">
                <svg width="40" height="40">
                  <circle r="18" cx="20" cy="20" stroke="#e0e0e0"></circle>
                  <circle r="18" cx="20" cy="20" stroke="#e63946" stroke-dasharray="113.097" stroke-dashoffset="28"></circle>
                </svg>
              </div>
              <div class="text-danger small mt-1">High</div>
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
                <tr><td>1</td><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>2</td><td>Client Meeting</td><td>May 7</td></tr>
                <tr><td>3</td><td>Update Website</td><td>May 10</td></tr>
                <tr><td>4</td><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>5</td><td>Client Meeting</td><td>May 7</td></tr>
                <tr><td>6</td><td>Update Website</td><td>May 10</td></tr>
                <tr><td>7</td><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>8</td><td>Client Meeting</td><td>May 7</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
  </div>

  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header-custom" style="background-color: #f77f00;">
        <img src="https://ijro.cerr.uz/user_image/avatar.jpg" class="workload-avatar" alt="David Williams">
        <h5 class="workload-title">Абдуллаев Руслан</h5>
      </div>
      <div class="card-body-custom">
        <div class="workload-section mb-3">
          <div>
            <div class="task-count">5</div>
            <span class="task-label">Задачи</span>
          </div>
          <div class="text-center">
            <div class="progress-ring mx-auto">
              <svg width="40" height="40">
                <circle r="18" cx="20" cy="20" stroke="#e0e0e0"></circle>
                <circle r="18" cx="20" cy="20" stroke="#f77f00" stroke-dasharray="113.097" stroke-dashoffset="50"></circle>
              </svg>
            </div>
            <div class="text-warning small mt-1">Medium</div>
          </div>
        </div>
        <div class="scrollable-task-table">
            <table class="table table-bordered task-table">
              <thead>
                <tr class="table-light">
                  <th>Название</th>
                  <th>Срок</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>Client Meeting</td><td>May 7</td></tr>
                <tr><td>Update Website</td><td>May 10</td></tr>
                <tr><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>Client Meeting</td><td>May 7</td></tr>
              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header-custom" style="background-color: #2a9d8f;">
        <img src="https://ijro.cerr.uz/user_image/avatar.jpg" class="workload-avatar" alt="James Riiler">
        <h5 class="workload-title">Ахматов Жамшид</h5>
      </div>
      <div class="card-body-custom">
        <div class="workload-section mb-3">
          <div>
            <div class="task-count">3</div>
            <span class="task-label">Задачи</span>
          </div>
          <div class="text-center">
            <div class="progress-ring mx-auto">
              <svg width="40" height="40">
                <circle r="18" cx="20" cy="20" stroke="#e0e0e0"></circle>
                <circle r="18" cx="20" cy="20" stroke="#2a9d8f" stroke-dasharray="113.097" stroke-dashoffset="68"></circle>
              </svg>
            </div>
            <div class="text-success small mt-1">Low</div>
          </div>
        </div>
        <div class="scrollable-task-table">
            <table class="table table-bordered task-table">
              <thead>
                <tr class="table-light">
                  <th>Название</th>
                  <th>Срок</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>Client Meeting</td><td>May 7</td></tr>
                <tr><td>Update Website</td><td>May 10</td></tr>
              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-header-custom" style="background-color: #2a9d8f;">
        <img src="https://ijro.cerr.uz/user_image/avatar.jpg" class="workload-avatar" alt="James Riiler">
        <h5 class="workload-title">Сапарматов Исломбек</h5>
      </div>
      <div class="card-body-custom">
        <div class="workload-section mb-3">
          <div>
            <div class="task-count">3</div>
            <span class="task-label">Задачи</span>
          </div>
          <div class="text-center">
            <div class="progress-ring mx-auto">
              <svg width="40" height="40">
                <circle r="18" cx="20" cy="20" stroke="#e0e0e0"></circle>
                <circle r="18" cx="20" cy="20" stroke="#2a9d8f" stroke-dasharray="113.097" stroke-dashoffset="68"></circle>
              </svg>
            </div>
            <div class="text-success small mt-1">Low</div>
          </div>
        </div>
        <div class="scrollable-task-table">
            <table class="table table-bordered task-table">
              <thead>
                <tr class="table-light">
                  <th>Название</th>
                  <th>Срок</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Prepare Report</td><td>May 5</td></tr>
                <tr><td>Client Meeting</td><td>May 7</td></tr>
                <tr><td>Update Website</td><td>May 10</td></tr>
              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>
</div>

@endsection
