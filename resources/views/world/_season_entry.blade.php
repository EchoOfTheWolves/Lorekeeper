<div class="row world-entry">
@if($season->imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $season->imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $season->imageUrl }}" class="world-entry-image" alt="{{ $season->name }}" /></a></div>
    @endif
    <div class="{{ $season->imageUrl ? 'col-md-9' : 'col-12' }}">
    <h3>{!! $season->displayName !!}</h3>
    @if($season->summary)<div class="text-muted"><i>" {!! $season->summary !!} " </i></div>@endif
        {!! $season->parsed_description !!}
    </div>
</div>