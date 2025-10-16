@extends('Admin.layouts.app')
@section('content')



<div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Link Repository</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($linkrepository)}} Link Repository.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('linkrepository.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
    <div class="card-inner">
    <table class="datatable-init-export nowrap table" data-export-title="Export">
    <thead>
        <tr>
       
            <th>S.No</th>
           
            <th>Listing</th>
            <th>Airbnb</th>
            <th>Gathern</th>
            <th>Booking.com</th>
            <th>Vrbo</th>
            <th>Status</th>
            <th>User Name</th> 
            <th>Actions</th> 
           
        </tr>
    </thead>
    <tbody>
        @foreach($linkrepository as $key => $item)
            <tr>
            
                <td>{{ ++$key }}</td>
               
             
                <!-- <td>{{ $item->hostdetail ? $item->hostdetail->name : 'N/A' }} {{$item->hostdetail ? $item->hostdetail->surname : 'N/A'}} -->
                <td>
                                @if ($item->listing != null)
                                    @php
                                        $lising_name = json_decode($item->listing->listing_json);
                                    @endphp
                                    {{ $lising_name->title }}
                                @else
                                    {{ $item->listing_title }}
                                @endif
                            </td>

             <td>{{ $item->airbnb }}</td>
             <td>{{ $item->gathern }}</td>
             <td>{{ $item->booking }}</td>
             <td>{{ $item->vrbo }}</td>
             <td>{{ $item->status }}</td>


              </td>
<td>{{ $item->userdetail ? $item->userdetail->name : 'N/A' }}</td>

                <td >
                    <!-- Add action buttons for View, Edit, and Delete -->
                     <div class="" style="display:flex;">

                    <a href="{{ route('linkrepository.edit', $item->id) }}" class="btn btn-warning btn-sm" style="margin:0px 5px;">Edit</a>
                     
            </div>




                </td>

                
            </tr>
        @endforeach
    </tbody>
</table>

    </div>
</div>
@endsection
