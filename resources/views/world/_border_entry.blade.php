<div class="row world-entry">
    <div class="col-md-3 world-entry-image">
        <div class="row no-gutters">
            <div class="col-6 col-md-12">
                <div class="user-avatar">
                    <a href="{{ $border->imageUrl }}" data-lightbox="entry" data-title="{{ $border->name }}" class=>
                        <img src="{{ $border->imageUrl }}" class="world-entry-image" alt="{{ $border->name }}" />
                    </a>
                </div>
            </div>
            <hr class="w-75 d-none d-md-block">
            <div class="col-6 col-md-12">
                <div id="test-{{ $border->id }}">
                    {!! $border->preview(Auth::check() ? Auth::user()->id : '') !!}
                </div>
            </div>
        </div>
        @if ($border->topLayers->count() && $border->bottomLayers->count())
            <hr class="w-75">
            <h5>Layer Preview</h5>
            <div class="form-group">
                {!! Form::label('Top Layer') !!}
                {!! Form::select('top_border', ['0' => 'Select Top Layer'] + $border->topLayers()->get()->pluck('settingsName', 'id')->toArray(), $border->id, ['class' => 'form-control', 'id' => 'top-' . $border->id]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('Bottom Layer') !!}
                {!! Form::select('bottom_border', ['0' => 'Select Bottom Layer'] + $border->bottomLayers()->get()->pluck('settingsName', 'id')->toArray(), $border->id, ['class' => 'form-control', 'id' => 'bottom-' . $border->id]) !!}
            </div>
        @endif
    </div>

    <div class="{{ $border->imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>
            {!! $border->displayName !!}@if (isset($border->idUrl) && $border->idUrl)
                <a href="{{ $border->idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
            @if ($border->admin_only)
                <i class="fas fa-user-lock text-warning" data-toggle="tooltip" title="This border is exclusive to staff members."></i>
            @else
                @if (!$border->is_default)
                    @if (Auth::check() && Auth::user()->hasBorder($border->id))
                        <i class="fas fa-lock-open" data-toggle="tooltip" title="You have this border!"></i>
                    @else
                        <i class="fas fa-lock" style="opacity:0.5" data-toggle="tooltip" title="You do not have this border."></i>
                    @endif
                @else
                    <i class="fas fa-user" data-toggle="tooltip" title="This border is automatically available to all users."></i>
                @endif
            @endif
        </h3>
        <div class="world-entry-text parsed-text">
            <div class="row">
                @if (isset($border->category) && $border->category)
                    <div class="col-md">
                        <p><strong>Category:</strong> {!! $border->category->displayName !!}</p>
                    </div>
                @endif
                @if (isset($border->borderArtist) && $border->borderArtist)
                    <div class="col-md">
                        <p><strong>Artist:</strong> {!! $border->borderArtist !!}</p>
                    </div>
                @endif
                @if ($border->hasItem())
                    <div class="col-md">
                        <p><strong>Obtained From:</strong> {!! $border->borderItem()->displayName !!}</p>
                    </div>
                @endif
            </div>
            {!! $border->parsed_description !!}
            <div class="container mt-2">
                @if ($border->variants->count())
                    <hr />
                    <h2 class="h4 pl-2">Variants</h2>
                    <div class="row">
                        @foreach ($border->variants as $variant)
                            <div class="col-md-3 col-6 text-center">
                                <div class="shop-image">
                                    {!! $variant->preview() !!}
                                </div>
                                <div class="shop-name mt-1 text-center">
                                    <h5>
                                        @if (!$variant->is_active)
                                            <i class="fas fa-eye-slash"></i>
                                        @endif
                                        {!! $variant->name !!}
                                    </h5>
                                    @if (isset($variant->borderArtist) && $variant->borderArtist)
                                        <div class="col-md">
                                            <p><strong>Artist:</strong> {!! $variant->borderArtist !!}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@if ($border->topLayers->count() && $border->bottomLayers->count())
    <script>
        $('#top-{{ $border->id }}').change(function() {
            refreshBorder{{ $border->id }}();
        });
        $('#bottom-{{ $border->id }}').change(function() {
            refreshBorder{{ $border->id }}();
        });

        function refreshBorder{{ $border->id }}() {
            var top = $('#top-{{ $border->id }}').val();
            var bottom = $('#bottom-{{ $border->id }}').val();
            var border = {{ $border->id }};
            $.ajax({
                type: "GET",
                url: "{{ url('world/check-border') }}?top=" + top + "&bottom=" + bottom + "&border=" + border,
                dataType: "text"
            }).done(function(res) {
                $("#test-{{ $border->id }}").html(res);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        };
    </script>
@endif
