@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Automated Triggers Listing</h3>
                
                
                <div class="nk-block-des text-soft">
                    <button id="manual_btn" class="btn btn-danger mt-2" onclick="selectUpdate(0)">Manual</button> &nbsp;&nbsp;
                    <button id="automated_btn" class="btn btn-primary mt-2" onclick="selectUpdate(1)">Automated</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
    <div class="card-inner">
        <table id="trgtbl" class="datatable-init-export nowrap table" data-export-title="Export">
            <thead>
            <tr>
                <th><input type="checkbox" id="select-all" class="form-check-input row-checkbox"></th>
                <th>S.No</th>
                <th>Listing</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($listings as $key=>$item)
            @php
            
            $btn_cls = $item->is_auto_trigger == 1 ? 'btn btn-primary' : 'btn btn-danger';
            $btn_txt = $item->is_auto_trigger == 1 ? 'Automated' : 'Manual';
            
            @endphp
                <tr>
                    <td><input type="checkbox" class="form-check-input row-checkbox" value="{{$item->listing_id}}"></td>
                    <td>{{++$key}}</td>
                    <td>{{$item->jsn_listing_name}}</td>
                    <td><span id="btn_{{$item->listing_id}}" class="{{$btn_cls}}" onclick="submitAutoTrigger('{{$item->listing_id}}', {{$item->is_auto_trigger}})">{{$btn_txt}}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    
<script>

    $(document).ready(function() {
        
        $('#select-all').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', isChecked);
        });
        
        $('#trgtbl tbody').on('change', '.row-checkbox', function() {
            var totalCheckboxes = $('.row-checkbox').length;
            var checkedCheckboxes = $('.row-checkbox:checked').length;
            $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
        });
        
    });
    
    function selectUpdate(select_type) {

        var selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if(selectedIds.length == 0){
            alert('Please select at least one option');
            return false;
        }
        
        if(select_type == 1){
            $('#automated_btn').text('Please wait...');
            $('#automated_btn').prop('disabled', true);
        } else{
            $('#manual_btn').text('Please wait...');
            $('#manual_btn').prop('disabled', true);
        }
        
        
        $.ajax({
            url: "{{ route('revenue-triggers.set-auto-trigger') }}",
            type: 'POST',
            data: {
                "listing_ids": selectedIds,
                "select_type": select_type
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // console.log(response);
                
                if(select_type == 1){
                    $('#automated_btn').text('Automated');
                    $('#automated_btn').prop('disabled', false);
                } else{
                    $('#manual_btn').text('Manual');
                    $('#manual_btn').prop('disabled', false);
                }
                
                if(response.success == 1){
                    
                    if(response.data.length > 0){
                        response.data.forEach(function(item, index) {
                            
                            var button = document.getElementById('btn_'+item.listing_id);
                            
                            if (item.set_auto == 1) {
                                button.textContent = 'Automated';
                                button.className = 'btn btn-primary';
                            } else {
                                button.textContent = 'Manual';
                                button.className = 'btn btn-danger';
                            }
                    
                        });
                    }
                }
                
                // alert(response.error);
            },
            error: function(xhr, status, error) {
                alert('There was an error processing the request: ' + error);
            }
        });
    
    }
    
    function submitAutoTrigger(listing_id, is_auto_trigger){
        
        $.ajax({
            url: "{{ route('revenue-triggers.set-auto-trigger') }}",
            type: 'POST',
            data: {
                "listing_id": listing_id,
                "is_auto_trigger": is_auto_trigger
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // console.log(response);
                
                if(response.success == 1){
                    
                    const button = document.getElementById('btn_'+listing_id);
                    
                    if (response.set_auto == 1) {
                        button.textContent = 'Automated';
                        button.className = 'btn btn-primary';
                    } else {
                        button.textContent = 'Manual';
                        button.className = 'btn btn-danger';
                    }
                }
                
                // alert(response.error);
            },
            error: function(xhr, status, error) {
                alert('There was an error processing the request: ' + error);
            }
        });
        
    }
</script>