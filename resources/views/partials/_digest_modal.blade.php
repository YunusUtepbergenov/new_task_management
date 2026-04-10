<!-- Create digest Modal -->
<div id="create_digest" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('documents.new_digest') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('digests.store') }}" id="createDigest" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{ __('documents.enter_name') }}</label>
                                <input class="form-control" name="name" type="text" placeholder="{{ __('documents.enter_name') }}">
                            </div>
                            <div class="alert alert-danger" id="digest_name"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{ __('documents.source') }}</label>
                                <input class="form-control" type="file" name="paper">
                            </div>
                            <div class="alert alert-danger" id="digest_description"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{ __('documents.source_link') }}</label>
                                <input class="form-control" name="link" type="text" placeholder="{{ __('documents.link') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{ __('documents.file_constraints') }}</label>
                                <input class="form-control" type="file" name="file">
                            </div>
                            <div class="alert alert-danger" id="digest_file"></div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">{{ __('documents.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Create digest Modal -->
