@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Apartment Data Shifting</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('listing.listingDataShiftUpdate') }}" method="POST">
                        @csrf
                        <div class="row gy-4">
                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="user_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="user_id" id="user_id"
                                            data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} {{ $user->surname }}
                                                    -
                                                    {{ $user->host_key }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div> --}}

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="listing_id_one">Old Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="listing_id_one" id="listing_id_one"
                                            data-placeholder="Select Old Apartment">
                                            <option value="" selected disabled>Select Old Apartment</option>
                                            @foreach ($listings as $listing)
                                                <option value="{{ $listing->id }}">{{ $listing->title }} -
                                                    {{ \Carbon\Carbon::parse($listing->created_at)->format('d-m-Y') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('listing_id_one')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="listing_id_two">New Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="listing_id_two" id="listing_id_two"
                                            data-placeholder="Select Old Apartment">
                                            <option value="" selected disabled>Select New Apartment</option>
                                            @foreach ($listings as $listing)
                                                <option value="{{ $listing->id }}">{{ $listing->title }} -
                                                    {{ \Carbon\Carbon::parse($listing->created_at)->format('d-m-Y') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('listing_id_two')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
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
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <table class="datatable-init-export nowrap table" data-export-title="Export">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Old Apartment</th>
                                <th>New Apartment</th>
                                <th>Created By</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listing_shifting as $key => $item)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $item->title_one }}</td>
                                    <td>{{ $item->title_two }}</td>
                                    <td>{{ $item->user->name . ' ' . $item->user->surname }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
