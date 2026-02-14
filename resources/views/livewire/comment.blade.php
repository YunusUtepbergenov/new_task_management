<div>
    <div class="tab-pane show active" id="comments">
        <div class="task-wrapper">
            <div class="task-list-container">
                <div class="task-list-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <form wire:submit="storeComment({{ $task->id }})" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <textarea class="form-control" wire:model="comment" rows="2" name="comment" id="comment" required></textarea>
                                    </div>

                                    <button class="btn btn-primary" wire:click="$refresh" style="float: right;">Submit</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-12">
                            @foreach ($comments as $cmt)
                                <div class="card" style="margin-bottom: 15px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="user d-flex flex-row align-items-center">
                                            <img src="{{ asset('assets/img/avatar.jpg') }}" width="30" class="user-img rounded-circle mr-2">
                                            <span>
                                                <small class="font-weight-bold text-secondary" style="font-size: 14px">{{ $task->username($cmt->user_id) }}</small>
                                                <small class="font-weight-bold" style="font-size: 13px; margin-left: 8px;">{{ $cmt->comment }}</small>
                                            </span>
                                        </div>
                                        <small style="margin-right: 10px;">{{ $cmt->created_at }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
