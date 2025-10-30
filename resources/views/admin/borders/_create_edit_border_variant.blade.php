{!! Form::open(['url' => 'admin/data/borders/edit/' . $border->id . '/' . $type . 's/' . ($variant->id ? 'edit/' . $variant->id : 'create'), 'files' => true]) !!}

@php
    $word = $type == 'variant' ? 'Variant' : (($type == 'top') ? 'Top Layer' : 'Bottom Layer')
@endphp

<h3>Basic Information</h3>

<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('name', $word . ' Name', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('name', $variant->name, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::checkbox('is_active', 1, $variant->is_active, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_active', 'Active?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Users can\'t see or select this border if it isn\'t visible.') !!}
    </div>
    @if ($variant->id)
        <div class="col-md-6 form-group">
            {!! Form::checkbox('delete', 1, false, [
                'class' => 'form-check-input',
                'data-toggle' => 'toggle',
            ]) !!}
            {!! Form::label('delete', 'Delete ' . $word , ['class' => 'form-check-label ml-3']) !!}
        </div>
    @endif
</div>

<div class="row">
    <div class="col-md">
        {!! Form::label('Border Artist (Optional)') !!} {!! add_help('Provide the artist\'s username if they are on site or, failing that, a link.') !!}
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::select('artist_id', $userOptions, $variant && $variant->artist_id ? $variant->artist_id : null, ['class' => 'form-control mr-2 selectize', 'placeholder' => 'Select a User']) !!}
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    {!! Form::text('artist_url', $variant && $variant->artist_url ? $variant->artist_url : '', ['class' => 'form-control mr-2', 'placeholder' => 'Artist URL']) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row">
    @if ($variant->id)
        <div class="col-md-4 text-center">
            <h3>Preview</h3>
            <div class="row no-gutters">
                <div class="col-6 col-md-12">
                    <div class="form-group">
                        <h5>Image</h5>
                        <div class="user-avatar">
                            <img src="{{ $variant->imageUrl }}" class="img-fluid" />
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-12">
                    <div class="form-group">
                        <h5>In Action</h5>
                        {!! $variant->preview() !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="col-md-7">
        <h3>Image</h3>
        <p>An image is required. You can't have a border with no image!</p>

        <div class="form-group">
            {!! Form::label($word . ' Image') !!}
            <div>{!! Form::file('image') !!}</div>
            <div class="text-muted">Supports .png and .gif</div>
        </div>
        <div class="form-group">
            {!! Form::label('Border Style (Required)') !!}{!! add_help('Choose how the border will display around an icon. It can display over or under the user\'s icon.') !!}
            {!! Form::select('border_style', ['0' => 'Under', '1' => 'Over'], $variant->border_style, [
                'class' => 'form-control',
                'placeholder' => 'Select a Type',
            ]) !!}
        </div>
    </div>
</div>

<div class="text-right">
    {!! Form::submit($variant->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}
<script>
    $(document).ready(function() {
        $('.selectize').selectize();
    });
</script>
