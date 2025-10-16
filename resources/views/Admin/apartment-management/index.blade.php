@extends('Admin.layouts.app')
@section('content')
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">Apartments</h3>
            <div class="nk-block-des text-soft">
                <p>You have total {{ count($apartments) }} apartments.</p>
            </div>
        </div>
        <div class="nk-block-head-content">
            <div class="toggle-wrap nk-block-tools-toggle">
                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                <div class="toggle-expand-content" data-content="pageMenu">
                    <ul class="nk-block-tools g-3">
                        <li class="nk-block-tools-opt">
                            <a href="{{ route('apartment-management.create') }}" class="btn btn-icon btn-primary">
                                <em class="icon ni ni-plus"></em>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-bordered card-preview">
    <div class="card-inner">
        <table class="datatable-init-export nowrap table" data-export-title="Apartments Export">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Type</th>
                    
                    <th>Apartment No</th>
                    
                    <th>Commission</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>District</th>
                    <th>Street</th>
                    <th>Postal</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Google Map</th>
                    <th>Guests</th>
                    <th>Bedrooms</th>
                    <th>Beds</th>
                    <th>Bathrooms</th>
                    <th>Amenities</th>
                    <th>Allow Pets</th>
                    <th>Self Check-in</th>
                    <th>Kitchen</th>
                    <th>Living Room</th>
                    <th>Corridor</th>
                    <th>Laundry</th>
                    <th>Outdoor</th>
                    <th>Cleaning Fee</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Price</th>
                 
                 
                   
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($apartments as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->apartment_type }}</td>
                  
                    <td>{{ $item->apartment_num }}</td>
                   
                    <td>{{ $item->commission_type }} - {{ $item->commission_value }}</td>
                    <td>{{ $item->address_line }}</td>
                    <td>{{ $item->city_name }}</td>
                    <td>{{ $item->district }}</td>
                    <td>{{ $item->street }}</td>
                    <td>{{ $item->postal }}</td>
                    <td>{{ $item->latitude }}</td>
                    <td>{{ $item->longitude }}</td>
                    <td>{{ $item->google_map }}</td>
                    <td>{{ $item->max_guests }}</td>
                    <td>{{ $item->bedrooms }}</td>
                    <td>{{ $item->beds }}</td>
                    <td>{{ $item->bathrooms }}</td>
                    <td>
                        @if($item->amenities && is_array($item->amenities))
                            @foreach($item->amenities as $a)
                                <span class="badge bg-success text-light">{{ $a }}</span>
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $item->is_allow_pets ? 'Yes' : 'No' }}</td>
                    <td>{{ $item->is_self_check_in ? 'Yes' : 'No' }}</td>
                    <td>{{ $item->kitchen }}</td>
                    <td>{{ $item->living_room }}</td>
                    <td>{{ $item->corridor }}</td>
                    <td>{{ $item->laundry_area }}</td>
                    <td>{{ $item->outdoor_area }}</td>
                    <td>{{ $item->cleaning_fee }}</td>
                    <td>{{ $item->discounts }}</td>
                    <td>{{ $item->tax }}</td>
                    <td>{{ $item->price }}</td>
                    
                   
                    <td>{{ $item->created_at->format('d-M-Y') }}</td>
                    <td>
                        <a href="{{ route('apartment-management.edit', $item->id) }}" class="btn btn-sm btn-primary" ><em class="icon ni ni-pen"></em></a>
                        <form method="POST" action="{{ route('apartment-management.destroy', $item->id) }}" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger" disabled><em class="icon ni ni-trash"></em></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
