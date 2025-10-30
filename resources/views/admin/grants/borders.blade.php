@extends('admin.layout')

@section('admin-title') Grant Borders @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Grant Borders' => 'admin/grants/borders']) !!}

<h1>Grant Borders</h1>

{!! Form::open(['url' => 'admin/grants/borders']) !!}

<h3>Basic Information</h3>

<div class="form-group">
    {!! Form::label('names[]', 'Username(s)') !!} {!! add_help('You can select up to 10 users at once.') !!}
    {!! Form::select('names[]', $users, null, ['id' => 'usernameList', 'class' => 'form-control', 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('Border(s)') !!} {!! add_help('Must have at least 1 border.') !!}
    <div id="borderList">
        <div class="d-flex mb-2">
            {!! Form::select('border_ids[]', $borders, null, ['class' => 'form-control mr-2 default border-select', 'placeholder' => 'Select Border']) !!}
            <a href="#" class="remove-border btn btn-danger mb-2 disabled"><i class="fas fa-times"></i></a>
        </div>
    </div>
    <div><a href="#" class="btn btn-primary" id="add-border">Add Border</a></div>
    <div class="border-row hide mb-2">
        {!! Form::select('border_ids[]', $borders, null, ['class' => 'form-control mr-2 border-select', 'placeholder' => 'Select Border']) !!}
        <a href="#" class="remove-border btn btn-danger mb-2"><i class="fas fa-times"></i></a>
    </div>
</div>

<div class="form-group">
    {!! Form::label('data', 'Reason (Optional)') !!} {!! add_help('A reason for the grant. This will be noted in the logs.') !!}
    {!! Form::text('data', null, ['class' => 'form-control', 'maxlength' => 400]) !!}
</div>

<div class="text-right">
    {!! Form::submit('Submit', ['class' => 'btn btn-primary btn-block']) !!}
</div>

{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usernameList').selectize({
            maxBorders: 10
        });
        $('.default.border-select').selectize();
        $('#add-border').on('click', function(e) {
            e.preventDefault();
            addBorderRow();
        });
        $('.remove-border').on('click', function(e) {
            e.preventDefault();
            removeBorderRow($(this));
        })
        function addBorderRow() {
            var $rows = $("#borderList > div")
            if($rows.length === 1) {
                $rows.find('.remove-border').removeClass('disabled')
            }
            var $clone = $('.border-row').clone();
            $('#borderList').append($clone);
            $clone.removeClass('hide border-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-border').on('click', function(e) {
                e.preventDefault();
                removeBorderRow($(this));
            })
            $clone.find('.border-select').selectize();
        }
        function removeBorderRow($trigger) {
            $trigger.parent().remove();
            var $rows = $("#borderList > div")
            if($rows.length === 1) {
                $rows.find('.remove-border').addClass('disabled')
            }
        }
    });
</script>

@endsection 