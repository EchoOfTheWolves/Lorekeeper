{!! Form::open(['url' => 'admin/character/image/' . $image->id . '/traits']) !!}
<div class="form-group">
    {!! Form::label('Species') !!}
    {!! Form::select('species_id', $specieses, $image->species_id, ['class' => 'form-control', 'id' => 'species']) !!}
</div>

    <div class="form-group" id="subtypes">
        {!! Form::label('Subtypes (Optional)') !!}
        {!! Form::select('subtype_ids[]', $subtypes, $image->subtypes()->pluck('subtype_id')->toArray(), ['class' => 'form-control', 'id' => 'subtype', 'multiple']) !!}
    </div>

<hr>
<h5>{{ ucfirst(__('transformations.transformations')) }}</h5>
<div class="form-group" id="transformations">
    {!! Form::label(ucfirst(__('transformations.transformation')) . ' (Optional)') !!}
    {!! Form::select('transformation_id', $transformations, $image->transformation_id, ['class' => 'form-control', 'id' => 'transformation']) !!}
</div>
<div class="form-group">
    {!! Form::label(ucfirst(__('transformations.transformation')) . ' Tab Info (Optional)') !!}{!! add_help('This is text that will show alongside the ' . __('transformations.transformation') . ' name in the tabs, so try to keep it short.') !!}
    {!! Form::text('transformation_info', $image->transformation_info, ['class' => 'form-control mr-2', 'placeholder' => 'Tab Info (Optional)']) !!}
</div>
<div class="form-group">
    {!! Form::label(ucfirst(__('transformations.transformation')) . ' Origin/Lore (Optional)') !!}{!! add_help('This is text that will show alongside the ' . __('transformations.transformation') . ' name on the image info area. Explains why the character takes this form, how, etc. Should be pretty short.') !!}
    {!! Form::text('transformation_description', $image->transformation_description, ['class' => 'form-control mr-2', 'placeholder' => 'Origin Info (Optional)']) !!}
</div>
<hr>

<div class="form-group">
    {!! Form::label('Character Rarity') !!}
    {!! Form::select('rarity_id', $rarities, $image->rarity_id, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Traits') !!}
    <div id="featureList">
        @foreach ($image->features as $feature)
            <div class="d-flex mb-2">
                {!! Form::select('feature_id[]', $features, $feature->feature_id, ['class' => 'form-control mr-2 feature-select original', 'placeholder' => 'Select Trait']) !!}
                {!! Form::text('feature_data[]', $feature->data, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
            </div>
        @endforeach
    </div>
    <div><a href="#" class="btn btn-primary" id="add-feature">Add Trait</a></div>
    <div class="feature-row hide mb-2">
        {!! Form::select('feature_id[]', $features, null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select Trait']) !!}
        {!! Form::text('feature_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
        <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('.original.feature-select').selectize();
        $('#add-feature').on('click', function(e) {
            e.preventDefault();
            addFeatureRow();
        });
        $('.remove-feature').on('click', function(e) {
            e.preventDefault();
            removeFeatureRow($(this));
        })

        function addFeatureRow() {
            var $clone = $('.feature-row').clone();
            $('#featureList').append($clone);
            $clone.removeClass('hide feature-row');
            $clone.addClass('d-flex');
            $clone.find('.remove-feature').on('click', function(e) {
                e.preventDefault();
                removeFeatureRow($(this));
            })
            $clone.find('.feature-select').selectize();
        }

        function removeFeatureRow($trigger) {
            $trigger.parent().remove();
        }
    });

<<<<<<< HEAD
    $( "#species" ).change(function() {
      var species = $('#species').val();
      var id = '<?php echo($image->id); ?>';
      $.ajax({
        type: "GET", url: "{{ url('admin/character/image/traits/subtype') }}?species="+species+"&id="+id, dataType: "text"
      }).done(function (res) { $("#subtypes").html(res); }).fail(function (jqXHR, textStatus, errorThrown) { alert("AJAX call failed: " + textStatus + ", " + errorThrown); });
    });
=======
    $("#species").change(function() {
        var species = $('#species').val();
        var id = '<?php echo $image->id; ?>';
        $.ajax({
            type: "GET",
            url: "{{ url('admin/character/image/traits/subtype') }}?species=" + species + "&id=" + id,
            dataType: "text"
        }).done(function(res) {
            $("#subtypes").html(res);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("AJAX call failed: " + textStatus + ", " + errorThrown);
        });
        $.ajax({
            type: "GET",
            url: "{{ url('admin/character/image/traits/transformation') }}?species=" + species + "&id=" + id,
            dataType: "text"
        }).done(function(res) {
            $("#transformations").html(res);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("AJAX call failed: " + textStatus + ", " + errorThrown);
        });
>>>>>>> cdd6b876c8cec544b8d30a5a301e7b6284b5ade7

    $( "#subtype" ).selectize({
        maxItems: config('lorekeeper.extensions.multiple_subtype_limit'),
    });
</script>
