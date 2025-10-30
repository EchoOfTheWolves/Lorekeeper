<div id="lootRowData" class="hide">
    <table class="table table-sm">
        <tbody id="lootRow">
            <tr class="loot-row">
                <td class="loot-row-select">
                    {!! Form::select('rewardable_id[]', $borders, null, [
                        'class' => 'form-control border-select',
                        'placeholder' => 'Select Border',
                    ]) !!}
                </td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
            </tr>
        </tbody>
    </table>

</div>
