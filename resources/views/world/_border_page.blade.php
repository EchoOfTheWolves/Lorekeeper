@extends('world.layout')

@section('title')
    {{ $border->name }}
@endsection

@section('meta-img')
    {{ $border->imageUrl }}
@endsection

@section('meta-desc')
    @if (isset($border->category) && $border->category)
        <p><strong>Category:</strong> {{ $border->category->name }}</p>
    @endif
    :: {!! substr(str_replace('"', '&#39;', $border->description), 0, 69) !!}
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'User Borders' => 'world/borders', $border->name => $border->idUrl]) !!}

    <div class="card mb-3">
        <div class="card-body p-2 p-md-3">
            @include('world._border_entry', [
                'imageUrl' => $border->imageUrl,
                'name' => $border->displayName,
                'description' => $border->parsed_description,
                'idUrl' => $border->idUrl,
            ])
        </div>
    </div>
@endsection
