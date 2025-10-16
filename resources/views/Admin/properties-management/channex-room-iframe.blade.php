@extends('Admin.layouts.app')
@section('content')
    <iframe style="width: 100%; height: 600px!important"
        src=env('CHANNEX_URL')."/auth/exchange?oauth_session_key={{$token}}&app_mo
de=headless&redirect_to=/rooms&property_id={{$property_id}}">
    </iframe>
@endsection
