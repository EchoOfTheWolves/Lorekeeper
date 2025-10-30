@extends('admin.layout')

@section('admin-title')
    Border Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Border Categories' => 'admin/data/border-categories']) !!}

    <h1>Border Categories</h1>

    <p>This is a list of border categories that will be used to sort borders. Creating border categories is entirely
        optional, but recommended if you have a lot of borders in the game.</p>
    <p>The sorting order reflects the order in which the border categories will be displayed in characters' border lists, as
        well as on the world pages.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/border-categories/create') }}"><i
                class="fas fa-plus"></i> Create New Border Category</a></div>
    @if (!count($categories))
        <p>No border categories found.</p>
    @else
        <table class="table table-sm category-table">
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-border" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/border-categories/edit/' . $category->id) }}"
                                class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/border-categories/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                borders: '.sort-border',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
