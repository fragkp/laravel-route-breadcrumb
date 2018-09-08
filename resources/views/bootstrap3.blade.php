@if ($breadcrumb->isNotEmpty())
    <ol class="breadcrumb">
        @foreach ($breadcrumb as $link)
            @if ($loop->last && ! $loop->first)
                <li class="active">{{ $link->title }}</li>
            @else
                <li>
                    <a href="{{ url($link->uri) }}" title="{{ $link->title }}">{{ $link->title }}</a>
                </li>
            @endif
        @endforeach
    </ol>
@endif
