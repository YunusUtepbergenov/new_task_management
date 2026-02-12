<div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="employeeTable">
                <table class="table custom-table article-table" id="myTable" style="overflow-y: auto;">
                    <thead id="employee">
                        <tr>
                            <th><span>&#8470;</span></th>
                            <th>
                             <form wire:submit="saveBlue">
                                <div class="form-row">
                                    <div class="col-sm-1">
                                        <span class="btn-colorselector" style="background-color: rgb(0, 32, 96);"></span>
                                    </div>
                                    <div class="offset-sm-2 col-sm-9">
                                      <input type="text" class="form-control form-control-sm" id="blue_input" wire:model='blue' autocomplete="off">
                                    </div>    
                                </div>
                              </form>
                            </th>
                            <th>
                                <form wire:submit="saveRed">
                                    <div class="form-row">
                                        <div class="col-sm-1">
                                            <span class="btn-colorselector" style="background-color: rgb(192, 0, 0);"></span>
                                        </div>
                                        <div class="offset-sm-2 col-sm-9">
                                          <input type="text" class="form-control form-control-sm" id="red_input" wire:model='red' autocomplete="off">
                                        </div>    
                                    </div>
                                </form>
                            </th>
                            <th>
                                <form wire:submit="saveGreen">
                                    <div class="form-row">
                                        <div class="col-sm-1">
                                            <span class="btn-colorselector" style="background-color: rgb(155, 187, 89);"></span>
                                        </div>
                                        <div class="offset-sm-2 col-sm-9">
                                            <input type="text" class="form-control form-control-sm" id="green_input" wire:model='green' autocomplete="off">
                                        </div>    
                                    </div>
                                </form>
                            </th>
                            <th>
                                <form wire:submit="saveViolet">
                                    <div class="form-row">
                                        <div class="col-sm-1">
                                            <span class="btn-colorselector" style="background-color: rgb(112, 48, 160);"></span>
                                        </div>
                                        <div class="offset-sm-2 col-sm-9">
                                          <input type="text" class="form-control form-control-sm" id="violet_input" wire:model='violet' autocomplete="off">
                                        </div>    
                                    </div>
                                  </form>
                            </th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @forelse ($words[$color] as $key=>$word)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                @if (isset($words['blue'][$key]))
                                    {{ $words['blue'][$key] }}
                                @else

                                @endif
                                </td>
                                <td>
                                    @if (isset($words['red'][$key]))
                                        {{ $words['red'][$key] }}
                                    @else

                                    @endif
                                </td>
                                <td>
                                    @if (isset($words['green'][$key]))
                                        {{ $words['green'][$key] }}
                                    @else

                                    @endif
                                </td>
                                <td>
                                    @if (isset($words['violet'][$key]))
                                        {{ $words['violet'][$key] }}
                                    @else

                                    @endif
                                </td>
                            </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
