@if ($transformation)
    {!! Form::open(['url' => 'admin/data/transformations/delete/' . $transformation->id]) !!}

    <p>You are about to delete the {{ __('transformations.transformation') }} <strong>{{ $transformation->name }}</strong>. This is not reversible. If characters that have this {{ __('transformations.transformation') }} exist, you will not be able to
        delete this {{ __('transformations.transformation') }}.</p>
    <p>Are you sure you want to delete <strong>{{ $transformation->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . ucfirst(__('transformations.transformation')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('transformations.transformation') }} selected.
@endif
