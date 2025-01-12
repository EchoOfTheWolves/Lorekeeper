@extends('admin.layout')

@section('admin-title') Shop Categories @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Shop Categories' => 'admin/data/shops/shop-categories']) !!}


<div class="text-right mb-3">
<a class="btn btn-secondary" href="{{ url('admin/data/shops') }}"> Shop Home</a>
</div>
<h1>Shop Categories</h1>

<p>This is a list of shop categories that will be used to sort shops on the shop page. Creating shop categories is entirely optional, but recommended if you have a lot of shops in the game.</p> 
<p>The sorting order reflects the order in which the shop categories will be displayed on the shop page.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/shops/shop-categories/create') }}"><i class="fas fa-plus"></i> Create New Shop Category</a></div>
@if(!count($categories))
    <p>No shop categories found.</p>
@else 
    <table class="table table-sm category-table">
        <tbody id="sortable" class="sortable">
            @foreach($categories as $category)
                <tr class="sort-shop" shop-id="{{ $category->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $category->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/shops/shop-categories/edit/'.$category->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/data/shops/shop-categories/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        shops: '.sort-shop',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"shop-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"shop-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection