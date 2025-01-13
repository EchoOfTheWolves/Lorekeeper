{!! Form::label(ucfirst(__('transformations.transformation')) . ' (Optional)') !!}
{!! Form::select('transformation_id', $transformations, old('transformation_id') ?: $transformation, ['class' => 'form-control', 'id' => 'transformation']) !!}
