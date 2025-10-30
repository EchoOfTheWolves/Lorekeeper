<div id="layers">
    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Top Border') !!}
            {!! Form::select('top_border_id', $top_layers, Auth::user()->top_border_id, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('Bottom Border') !!}
            {!! Form::select('bottom_border_id', $bottom_layers, Auth::user()->bottom_border_id, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>
