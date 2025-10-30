<h3>Borders</h3>

<a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
    {!! Form::checkbox('all_borders', 1, !is_array($tag->getData()), [
        'class' => 'form-check-input',
        'data-toggle' => 'toggle',
        'data-on' => 'Take from all unlockable Borders',
        'data-off' => 'Use specific Borders',
    ]) !!}
</a>
<br /><br />
<div class="collapse {{ is_array($tag->getData()) ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="addLoot">Add Border</a>
        </div>
        <table class="table table-sm" id="lootTable">
            <thead>
                <tr>
                    <th width="50%">Border</th>
                    <th width="10%"></th>
                </tr>
            </thead>
            <tbody id="lootTableBody">
                @if (is_array($tag->getData()))
                    @foreach ($tag->getData() as $loot)
                        <tr class="loot-row">
                            <td class="loot-row-select">
                                {!! Form::select('rewardable_id[]', $borders, $loot->rewardable_id, [
                                    'class' => 'form-control border-select selectize',
                                    'placeholder' => 'Select Border',
                                ]) !!}
                            </td>
                            <td class="text-right"><a href="#"
                                    class="btn btn-danger remove-loot-button">Remove</a></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<hr>
