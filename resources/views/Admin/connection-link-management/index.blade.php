@extends('Admin.layouts.app')
@section('content')
    <div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Connection Links</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($connection_links)}} Connection Links.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('connect-link-management.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
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
                <th>Host ID</th>
                <th>Host Name</th>
                <th>Created_at</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($connection_links as $key=>$item)
                <tr>
                    <td>{{++$key}}</td>
                     <td>{{isset($item->user->id) ? $item->user->id : ''}}</td>
                            <td>{{isset($item->user->name) ? $item->user->name : ''}} {{isset($item->user->surname) ? $item->user->surname : ''}}</td>
{{--                    <td>{{$item->url}}</td>--}}
                    <td>{{ $item->created_at->format('d-M-Y') }}</td>
                    <td>
                        <button id="copyButton" onclick="copyButtonValue(`{{$item->url}}`)" class="btn btn-primary btn-sm">
                            <em class="icon ni ni-clip"></em>
                        </button>
{{--                        <a href="{{route('connect-link-management.destroy', $item)}}" class="btn btn-danger btn-sm">--}}
{{--                            <em class="icon ni ni-trash"></em>--}}
{{--                        </a>--}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
    <script>
        function copyButtonValue(url) {
            // Create a temporary input element
            var input = document.createElement("input");
            input.setAttribute("value", url);
            document.body.appendChild(input);

            // Select the input text
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices

            // Copy the selected text
            document.execCommand("copy");

            // Remove the temporary input
            document.body.removeChild(input);
            alert("Url Copied !!")
            // Change button text to indicate copied
            // var button = document.getElementById("copyButton");
            // button.innerText = "URL Copied!";
            // setTimeout(function() {
            //     button.innerText = "Copy URL";
            // }, 1500); // Reset button text after 1.5 seconds
        }
    </script>

@endsection
