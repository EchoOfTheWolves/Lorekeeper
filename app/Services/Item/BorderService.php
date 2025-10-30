<?php namespace App\Services\Item;

use App\Services\Service;

use DB;

use App\Services\InventoryManager;

use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Models\Border\Border;

class BorderService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Border Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of box type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [
            'borders' => Border::base()->orderBy('name')
                ->where('is_default', 0)->where('admin_only', 0)
                ->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        if (isset($tag->data['all_borders'])) {
            return 'All';
        }
        // if($tag->data)
        $rewards = [];
        if ($tag->data) {
            $assets = parseAssetData($tag->data);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id' => $id,
                        'quantity' => $asset['quantity'],
                    ];
                }
            }
        }
        return $rewards;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        DB::beginTransaction();

        try {
            // If there's no data, return.
            if (!isset($data['rewardable_id']) && !isset($data['all_borders'])) {
                return true;
            }
            if (isset($data['all_borders'])) {
                $assets = ['all_borders' => 1];
            } else {
                // The data will be stored as an asset table, json_encode()d.
                // First build the asset table, then prepare it for storage.

                $type = 'App\Models\Border\Border';

                $assets = createAssetsArray();
                foreach ($data['rewardable_id'] as $key => $r) {
                    $asset = $type::find($data['rewardable_id'][$key]);
                    addAsset($assets, $asset, 1);
                }
                $assets = getDataReadyAssets($assets);
            }

            $tag->update(['data' => json_encode($assets)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function act($stacks, $user, $data)
    {
        DB::beginTransaction();

        try {
            $firstData = $stacks->first()->item->tag('border')->data;
            if (isset($firstData['all_borders']) && $firstData['all_borders']) {
                $borderOptions = Border::base()->where('is_active', 1)->where('is_default', 0)->where('admin_only', 0)
                    ->whereNotIn('id', $user->borders->pluck('id')->toArray())
                    ->get();
            } elseif (isset($firstData['borders']) && count($firstData['borders'])) {
                $borderOptions = Border::base()->find(array_keys($firstData['borders']))
                    ->where('is_active', 1)->where('is_default', 0)->where('admin_only', 0)
                    ->whereNotIn('id', $user->borders->pluck('id')->toArray());
            }

            $options = $borderOptions->pluck('id')->toArray();
            if (!count($options)) {
                throw new \Exception('There are no more options for this border redemption item.');
            }
            if (count($options) < array_sum($data['quantities'])) {
                throw new \Exception('You have selected a quantity too high for the quantity of borders you can unlock with this item.');
            }

            foreach ($stacks as $key => $stack) {
                // We don't want to let anyone who isn't the owner of the box open it,
                // so do some validation...
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Next, try to delete the box item. If successful, we can start distributing rewards.
                if ((new InventoryManager())->debitStack($stack->user, 'Border Redeemed', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        $random = array_rand($options);
                        $thisBorder['borders'] = [$options[$random] => 1];
                        unset($options[$random]);
                        // Distribute user rewards
                        if (
                            !($rewards = fillUserAssets(parseAssetData($thisBorder), $user, $user, 'Border Redemption', [
                                'data' => 'Redeemed from ' . $stack->item->name,
                            ]))
                        ) {
                            throw new \Exception('Failed to open border redemption item.');
                        }
                        flash($this->getBorderRewardsString($rewards));
                    }
                }
            }
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  array                  $rewards
     * @return string
     */
    private function getBorderRewardsString($rewards)
    {
        $results = 'You have unlocked the following border: ';
        $result_elements = [];
        foreach ($rewards as $assetType) {
            if (isset($assetType)) {
                foreach ($assetType as $asset) {
                    array_push($result_elements, $asset['asset']->displayName);
                }
            }
        }
        return $results . implode(', ', $result_elements);
    }
}
