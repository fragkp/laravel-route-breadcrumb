@if ($breadcrumb->isNotEmpty())
    <nav class="breadcrumb" aria-label="breadcrumbs">
        <ul>
            @foreach ($breadcrumb as $link)
                @if ($loop->last && ! $loop->first)
                    <li class="is-active">
                        <a href="#" aria-current="page">{{ $link->title }}</a>
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
