@php 
$weather = \App\Models\Weather\Weather::where('id', Settings::get('site_weather'))->first();
$season = \App\Models\Weather\WeatherSeason::where('id', Settings::get('site_season'))->first();
@endphp
@if($weather)
<br>
<div class="card mb-4">
    <div class="card-body text-center">
        The weather is currently...
        <h5>{!! $weather->displayName !!}</h5>
        @if($weather->has_image)
        <img src="{{ $weather->imageUrl }}" alt="{{ $weather->name }}" class="img-thumbnail" />
        @endif
        @if($weather->summary)<div class="text-muted"><i>" {!! $weather->summary !!} " </i></div>@endif
    </div>
</div>
@endif

@if($season)
<div class="card mb-4">
    <div class="card-body text-center">
    Current Season: 
    <h5>{!! $season->displayName !!}</h5>
        @if($season->has_image)
        <img src="{{ $season->imageUrl }}" alt="{{ $season->name }}" class="img-thumbnail" />
        @endif
        @if($season->summary)<div class="text-muted"><i>" {!! $season->summary !!} " </i></div>@endif
    </div>
</div>
@endif