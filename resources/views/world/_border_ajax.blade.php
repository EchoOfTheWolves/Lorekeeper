<div id="test-{{ $border->id }}">
    {!! $border->previewLayers($top, $bottom, Auth::check() ? Auth::user()->id : '') !!}
</div>
