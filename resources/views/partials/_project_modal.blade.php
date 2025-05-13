<!-- Create Project Modal -->
<div id="create_project" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Недельный план</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('tasks.bulk_store') }}" method="POST" id="createProject">
                    @csrf
                    <div id="task-entries">
                        <!-- Task row group -->
                      <div class="task-group border p-3 mb-3 bg-light rounded">
                        <div class="form-row align-items-end">
                          <div class="form-group col-md-2">
                            <label>Категория</label>
                            <select class="form-control select2" name="tasks[0][task_score]">
                              @foreach ($scoresGrouped as $group => $items)
                                  <optgroup label="{{ $group }}">
                                      @foreach ($items as $type)
                                          <option value="{{ $type->id }}">{{ $type->name }} (Макс: {{ $type->max_score }})</option>
                                      @endforeach
                                  </optgroup>
                              @endforeach
                            </select>
                          </div>
                          <div class="form-group col-md-4">
                            <label>Название</label>
                            <input type="text" name="tasks[0][name]" class="form-control" required>
                          </div>
                          <div class="form-group col-md-2">
                            <label>Крайний срок</label>
                            <div class="cal-icon">
                                <input name="tasks[0][deadline]" class="form-control datetimepicker" required>
                            </div>
                          </div>
                          <div class="form-group col-md-3">
                            <label>Ответственный</label>
                            <select name="tasks[0][workers][]" class="form-control select2" multiple required>
                              @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                                @foreach ($sectors as $sector)
                                  <optgroup label="{{ $sector->name }}">
                                      @foreach ($sector->users as $user)
                                          <option value="{{ $user->id }}">{{ $user->name }}</option>
                                      @endforeach
                                  </optgroup>
                                @endforeach

                              @elseif(Auth::user()->isDeputy())
                                @foreach ($sectors as $sector)
                                  <optgroup label="{{ $sector->name }}">
                                      @foreach ($sector->users as $user)
                                          @if (!$user->isDirector() && !$user->isDeputy())
                                              <option value="{{ $user->id }}">{{ $user->name }}</option>                                                        
                                          @endif
                                      @endforeach
                                  </optgroup>
                                @endforeach
                              
                              @elseif(Auth::user()->isHead())
                                @foreach (Auth::user()->sector->users()->where('leave', 0)->orderBy('role_id', 'ASC')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach

                              @endif
                            </select>
                          </div>
                          <div class="form-group col-md-1 text-right">
                            <button type="button" class="btn btn-danger remove-task">×</button>
                          </div>
                        </div>
                      </div>
                    </div>

                      <div class="text-center my-3">
                        <button type="button" id="add-task" class="btn btn-outline-secondary">
                            Добавить еще одну задачу
                        </button>
                      </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Создать Задачи</button>
                    </div>
                </form>

                <div id="task-template" class="d-none">
                    <div class="task-group border p-3 mb-3 bg-light rounded">
                      <div class="form-row align-items-end">
                        <div class="form-group col-md-2">
                          <label>Категория</label>
                          <select class="form-control select2" name="tasks[__index__][task_score]">
                            @foreach ($scoresGrouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} (Макс: {{ $type->max_score }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group col-md-4">
                          <label>Название</label>
                          <input type="text" name="tasks[__index__][name]" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                          <label>Крайний срок</label>
                          <div class="cal-icon">
                              <input name="tasks[__index__][deadline]" class="form-control datetimepicker" required>
                          </div>
                        </div>
                        <div class="form-group col-md-3">
                          <label>Ответственный</label>
                          <select name="tasks[__index__][workers][]" class="form-control select2" multiple required>
                                @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                                  @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </optgroup>
                                  @endforeach

                                @elseif(Auth::user()->isDeputy())
                                  @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if (!$user->isDirector() && !$user->isDeputy())
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>                                                        
                                            @endif
                                        @endforeach
                                    </optgroup>
                                  @endforeach
                                
                                @elseif(Auth::user()->isHead())
                                  @foreach (Auth::user()->sector->users()->where('leave', 0)->orderBy('role_id', 'ASC')->get() as $user)
                                      <option value="{{ $user->id }}">{{ $user->name }}</option>
                                  @endforeach

                                @endif
                          </select>
                        </div>

                        <div class="form-group col-md-1 text-right">
                          <button type="button" class="btn btn-danger remove-task">×</button>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Create Project Modal -->
