@extends('world.layout')

@section('title')
    Borders
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'User Borders' => 'world/borders']) !!}
    <h1>Borders</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('border_category_id', $categories, Request::get('border_category_id'), [
                    'class' => 'form-control',
                ]) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('is_default', $is_default, Request::get('is_default'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('artist', $artists, Request::get('artist'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'category' => 'Sort by Category',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'category',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $borders->render() !!}
    @foreach ($borders as $border)
        <div class="card mb-3">
            <div class="card-body p-2 p-md-3">
                @include('world._border_entry', ['border' => $border])
            </div>
        </div>
    @endforeach
    {!! $borders->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $borders->total() }} result{{ $borders->total() == 1 ? '' : 's' }}
        found.</div>
@endsection
