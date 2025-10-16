@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Finance Report</h3>
                <div class="nk-block-des text-soft">

                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em
                            class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <a href="{{ route('reports.finance.soa.create') }}" class="btn btn-icon btn-primary"><em
                                        class="icon ni ni-plus"></em></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <form action="{{ route('finance.financeReportIndex') }}" method="GET">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label" for="daterange">Date</label>
                            <input type="text" class="form-control" name="daterange" id="daterange" required
                                value="01/01/2018 - 01/15/2018" />
                            @error('daterange')
                                <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group text-left ">
                            <label for=""></label>
                            <button type="submit" class="btn btn-primary mt-4">Submit</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
        var start = moment().subtract(2, 'days');
        var end = moment();
        var start = moment().subtract(2, 'days');
        var end = moment();

        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: start,
            endDate: end,
            // minDate: moment(), // Disable dates before today
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format(
                'YYYY-MM-DD'));
        });
    </script>
@endsection
