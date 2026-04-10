<!-- Create Scrape Modal -->
<div id="create_scrape" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('ui.research.add_file') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('scrape.upload') }}" id="createScrape" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{ __('ui.research.enter_name') }}</label>
                                <input class="form-control" name="name" type="text" placeholder="{{ __('ui.research.enter_name') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ __('ui.research.category') }}</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="category">
                                <option value="houses">{{ __('ui.research.real_estate') }}</option>
                                <option value="jobs">{{ __('ui.research.jobs') }}</option>
                                <option value="cars">{{ __('ui.research.cars') }}</option>
                                <option value="products">{{ __('ui.research.products') }}</option>
                                <option value="corruption">{{ __('ui.research.corruption_survey') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ __('ui.research.date') }}</label>
                        <div class="col-sm-4">
                            <input class="form-control datetimepicker" name="date">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>{{ __('ui.research.file_constraints') }}</label>
                                <input class="form-control" type="file" name="file">
                            </div>
                        </div>
                    </div>

                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">{{ __('ui.research.add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Create Scrape Modal -->
