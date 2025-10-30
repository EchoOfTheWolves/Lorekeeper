@extends('admin.layout')

@section('admin-title')
    Borders
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Borders' => 'admin/data/borders']) !!}

    <h1>Borders</h1>

    <p>
        This is a list of borders in the game. Borders exist in tandem with their paired item, via which they can be aquired
        by users and used to unlock the associated border for characters.
    </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/border-categories') }}"><i class="fas fa-folder"></i> Border
            Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/borders/create') }}"><i class="fas fa-plus"></i> Create New
            Border</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($borders))
        <p>No borders found.</p>
    @else
        {!! $borders->render() !!}

        <div class="row ml-md-2 mb-4">
            <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                <div class="col-3 col-md-4 font-weight-bold">Name</div>
                <div class="col-3 col-md-2 font-weight-bold">Border Style</div>
                <div class="col-3 col-md-1 font-weight-bold">Is Default?</div>
                <div class="col-3 col-md-1 font-weight-bold">Staff Only?</div>
                <div class="col-3 col-md-2 font-weight-bold">Item?</div>
            </div>
            @foreach ($borders as $border)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
                    <div class="col-3 col-md-4"> {{ $border->name }}
                        @if (!$border->is_active)
                            <i class="fas fa-eye-slash mr-1"></i>
                        @endif
                    </div>
                    <div class="col-3 col-md-2"> {!! $border->border_style ? 'Over' : 'Under' !!} </div>
                    <div class="col-3 col-md-1"> {!! $border->is_default ? '<i class="text-success fas fa-check"></i>' : '-' !!} </div>
                    <div class="col-3 col-md-1"> {!! $border->admin_only ? '<i class="text-success fas fa-check"></i>' : '-' !!} </div>
                    <div class="col-3 col-md-2"> {!! $border->hasItem() ? $border->borderItem()->displayName : '-' !!} </div>
                    <div class="col col-md text-right">
                        <a href="{{ url('admin/data/borders/edit/' . $border->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        {!! $borders->render() !!}
    @endif

@endsection

@section('scripts')
    @parent
@endsection
