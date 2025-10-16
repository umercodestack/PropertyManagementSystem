@extends('Admin.layouts.app')
@section('content')

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 30px;
    }

    .dropdown-container {
        position: relative;
    }

    .dropdown-selected {
        border: 1px solid #ccc;
        padding: 10px;
        cursor: pointer;
        background-color: #fff;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .dropdown-selected span {
        background: #007bff;
        color: white;
        padding: 5px 8px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
    }

    .dropdown-selected input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 50px;
        font-size: 14px;
    }

    .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        border: 1px solid #ccc;
        border-top: none;
        max-height: 255px;
        overflow-y: auto;
        background-color: #fff;
        z-index: 10;
        display: none;
    }

    .dropdown-options div {
        padding: 10px;
        cursor: pointer;
    }

    .dropdown-options div:hover {
        background-color: #f1f1f1;
    }
</style>
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Update Voucher</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('voucher-management.update', $voucher->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="listing_ids" multiple id="selectedItemsInput" />
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="listing_id">Listings <small><b>(The voucher will be applied to)</b></small></label>
                                    <div class="dropdown-container" id="multiSelect">
                                        <div class="dropdown-selected" id="selectedItems">
                                            <input type="text" id="searchInput" placeholder="Search Listing..." autocomplete="off">
                                        </div>
                                        <div class="dropdown-options" id="optionsList"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Voucher Name</label>
                                    <input type="text" class="form-control" id="voucher_code" name="voucher_code" placeholder="Voucher Name" value="{{ $voucher->voucher_code }}" autocomplete="off" required onkeydown="return event.key !== ' '">
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Voucher Start Date <small><b>(Date voucher should go live)</b></small></label>
                                    <input type="date" class="form-control" id="voucher_start_date" name="voucher_start_date" placeholder="Voucher Start Date" value="{{ $voucher->voucher_start_date }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Voucher End Date <small><b>(Date voucher will end)</b></small></label>
                                    <input type="date" class="form-control" id="voucher_end_date" name="voucher_end_date" placeholder="Voucher End Date" value="{{ $voucher->voucher_end_date }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="role_id">Type of Voucher <small><b>(Flat discount or Percentage discount)</b></small></label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="discount_type" id="discount_type" data-placeholder="Select an option" required>
                                            <option value="" {{empty($voucher->discount_type) ? 'selected' : ''}}>Please select an option</option>
                                            <option value="amount" {{$voucher->discount_type == 'amount' ? 'selected' : ''}}>Flat Discount</option>
                                            <option value="percentage" {{$voucher->discount_type == 'percentage' ? 'selected' : ''}}>Percentage Discount</option>
                                        </select>
                                        @error('is_enabled')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Discount
                                    <br><small><b>• Flat Discount:</b> Enter total discount amount (e.g., 100).</small>
                                    <br><small><b>• Percentage Discount:</b> Enter discount rate (e.g., 10% or 15%).</small>
                                    </label>
                                    <input type="number" class="form-control" id="discount" name="discount" placeholder="Discount" value="{{ $voucher->discount }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Max Percentage Discount Value <small><b>(Upper limit of discount value)</b></small></label>
                                    <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" placeholder="Max Discount Amount" value="{{ $voucher->max_discount_amount }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Minimum Order Amount <small><b>(Minimum Order value for voucher to be applicable)</b></small></label>
                                    <input type="number" class="form-control" id=">min_order_amount" name="min_order_amount" placeholder="Minimum Order Amount" value="{{ $voucher->min_order_amount }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Minimum Number of Nights <small><b>(Minimum number of nights for voucher to be applicable)</b></small></label>
                                    <input type="number" class="form-control" id=">min_number_nights" name="min_number_nights" placeholder="Minimum Number of Nights" value="{{ $voucher->min_number_nights }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="role_id">Discount Applied on <small><b>(Per Night/Total Amount)</b></small></label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="discount_applied_on" id="discount_applied_on" data-placeholder="Select an option" required>
                                            <option value="" {{empty($voucher->discount_applied_on) ? 'selected' : ''}}>Please select an option</option>
                                            <option value="per_night_value" {{$voucher->discount_applied_on == 'per_night_value' ? 'selected' : ''}}>Per Night Value</option>
                                            <option value="total_amount" {{$voucher->discount_applied_on == 'total_amount' ? 'selected' : ''}}>Total Amount</option>
                                        </select>
                                        @error('is_enabled')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Maximum number of uses <small><b>(how many times a voucher can be used)</b></small> </label>
                                    <input type="number" class="form-control" id=">voucher_usage_limit" name="voucher_usage_limit" placeholder="Voucher Usage Limit" value="{{ $voucher->voucher_usage_limit }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="name">Maximum number of uses per guest <small><b>(how many times can one guest use the same voucher)</b></small> </label>
                                    <input type="number" class="form-control" id=">max_uses_per_guest" name="max_uses_per_guest" placeholder="Maximum number of uses per guest" value="{{ $voucher->max_uses_per_guest }}" autocomplete="off" required>
                                    @error('name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="role_id">Active</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select" name="is_enabled" id="is_enabled" data-placeholder="Select an option">
                                            <option value="1" {{$voucher->is_enabled == 1 ? 'selected' : ''}}>Yes</option>
                                            <option value="0" {{$voucher->is_enabled == 0 ? 'selected' : ''}}>No</option>
                                        </select>
                                        @error('is_enabled')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const discountType = document.getElementById('discount_type');
            const maxDiscountField = document.getElementById('max_discount_amount');

            function toggleMaxDiscountRequirement() {
                if (discountType.value === 'percentage') {
                    maxDiscountField.setAttribute('required', 'required');
                } else {
                    maxDiscountField.removeAttribute('required');
                }
            }

            toggleMaxDiscountRequirement();

            discountType.addEventListener('change', toggleMaxDiscountRequirement);
        });
      
        const options = JSON.parse(@json($listings));
        
        const preSelectedValues = JSON.parse(@json($selected_listing_ids));
        
        // const options = [
        //     { value: 'apple', label: 'Apple' },
        //     { value: 'banana', label: 'Banana' },
        //     { value: 'orange', label: 'Orange' },
        //     { value: 'mango', label: 'Mango' },
        //     { value: 'grape', label: 'Grape' },
        //     { value: 'all', label: 'Select All' } 
        // ];
        
        // const preSelectedValues = [];// ['banaana', 'manago'];
        
        const selectedItemsDiv = document.getElementById('selectedItems');
        const searchInput = document.getElementById('searchInput');
        const optionsList = document.getElementById('optionsList');
        const hiddenInput = document.getElementById('selectedItemsInput');
        
        let selectedValues = options.filter(opt => preSelectedValues.includes(opt.value));
        
        function renderOptions(filter = '') {
        optionsList.innerHTML = '';
        
        const isSelectAllSelected = selectedValues.some(v => v.value === 'all');
        
        options
          .filter(opt =>
            opt.label.toLowerCase().includes(filter.toLowerCase()) &&
            !selectedValues.some(sel => sel.value === opt.value)
          )
          .forEach(opt => {
            const div = document.createElement('div');
            div.textContent = opt.label;
            div.setAttribute('data-value', opt.value);
        
            if (isSelectAllSelected && opt.value !== 'all') {
              div.classList.add('disabled');
            }
        
            div.onclick = () => {
              if (opt.value === 'all') {
                selectedValues = [opt];
              } else {
                if (isSelectAllSelected) return;
                selectedValues.push(opt);
              }
        
              updateSelectedItems();
              renderOptions(searchInput.value);
            };
        
            optionsList.appendChild(div);
          });
        }
        
        function updateSelectedItems() {
        selectedItemsDiv.innerHTML = '';
        selectedValues.forEach(val => {
          const span = document.createElement('span');
          span.textContent = val.label;
          span.title = `Click to remove ${val.label}`;
          span.onclick = (e) => {
            e.stopPropagation();
            selectedValues = selectedValues.filter(v => v.value !== val.value);
            updateSelectedItems();
            renderOptions(searchInput.value);
          };
          selectedItemsDiv.appendChild(span);
        });
        selectedItemsDiv.appendChild(searchInput);
        searchInput.focus();
        updateHiddenInput();
        }
        
        function updateHiddenInput() {
        hiddenInput.value = selectedValues.map(v => v.value).join(',');
        }
        
        selectedItemsDiv.onclick = () => {
        optionsList.style.display = optionsList.style.display === 'block' ? 'none' : 'block';
        renderOptions(searchInput.value);
        };
        
        searchInput.addEventListener('keyup', () => {
        renderOptions(searchInput.value);
        });
        
        document.addEventListener('click', (e) => {
        if (!document.getElementById('multiSelect').contains(e.target)) {
          optionsList.style.display = 'none';
        }
        });
        
        function syncDropdownSelection() {
        updateHiddenInput();
        return true;
        }
        
        
        updateSelectedItems();
        renderOptions();
    </script>
@endsection
