@if ($breadcrumb->isNotEmpty())
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @foreach ($breadcrumb as $link)
                @if ($loop->last && ! $loop->first)
                    <li class="breadcrumb-item active" aria-current="page">{{ $link->title }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ url($link->uri) }}" title="{{ $link->title }}">{{ $link->title }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
