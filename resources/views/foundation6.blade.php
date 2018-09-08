@if ($breadcrumb->isNotEmpty())
    <nav aria-label="You are here:" role="navigation">
        <ul class="breadcrumbs">
            @foreach ($breadcrumb as $link)
                @if ($loop->last && ! $loop->first)
                    <li>
                        <span class="show-for-sr">Current:</span> {{ $link->title }}
                    </li>
                @else
                    <li>
                        <a href="{{ url($link->uri) }}" title="{{ $link->title }}">{{ $link->title }}</a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>
@endif
