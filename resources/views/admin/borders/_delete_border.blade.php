@if($border)
    {!! Form::open(['url' => 'admin/data/borders/delete/'.$border->id]) !!}

    <p>You are about to delete the border <strong>{{ $border->name }}</strong>. This is not reversible. If this border exists in at least one character's possession, you will not be able to delete this border. Consider making its corresponding item unavailable instead.</p>
    <p>Are you sure you want to delete <strong>{{ $border->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Border', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid border selected.
@endif