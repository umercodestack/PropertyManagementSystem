@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Host Type</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{route('host-type-management.update', $hostType->id)}}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="module_name">Module Name</label>
                                    <input type="text" class="form-control" id="module_name" name="module_name" value="{{$hostType->module_name}}" placeholder="Module Name">
                                    @error('module_name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount_type">Amount Type</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="amount_type" id="amount_type"  data-placeholder="Amount Type">
                                            <option value="" selected disabled>Amount Type</option>
                                            <option value="percentage" {{$hostType->amount_type == 'percentage' ? 'selected': ''}}>Percentage</option>
                                            <option value="fixed" {{$hostType->amount_type == 'fixed' ? 'selected': ''}}>Fixed</option>
                                        </select>
                                        @error('amount_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="amount">Amount</label>
                                    <input type="number" class="form-control" id="amount" name="amount" value="{{$hostType->amount}}" onblur="checkPercentageValue(this.value, `{{$hostType->amount}}`)" placeholder="Amount">
                                    @error('amount')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 align-items-center">
                                <label class="form-label mb-2" for="amount">Charge Type</label>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" {{isset($chargeTypes[0]->charge_type) && $chargeTypes[0]->charge_type == 'booking' ? 'checked' : ''}} name="charge_type[]" id="booking" value="booking">
                                        <label class="custom-control-label" for="booking">Booking</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" {{isset($chargeTypes[1]->charge_type) && $chargeTypes[1]->charge_type == 'task' ? 'checked' : ''}} name="charge_type[]" id="task" value="task">
                                        <label class="custom-control-label" for="task">Task</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

        let amount_type = document.getElementById('amount_type');
        let amount = document.getElementById('amount');
        function checkPercentageValue(e, previousValue) {
            // alert(amount_type.value);
            if(amount_type.value === 'percentage' && e > 100) {
                // alert("In")
                alert("percentage Value Can Not Be Greater Than 100 !!!");
                amount.value = previousValue;
            }
        }
    </script>
@endsection
