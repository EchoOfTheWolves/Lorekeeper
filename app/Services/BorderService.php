<?php
namespace App\Services;

use App\Models\Border\Border;
use App\Models\Border\BorderCategory;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;
use App\Models\User\User;
use App\Models\User\UserBorder;
use App\Services\Service;
use Carbon\Carbon;
use DB;
use Image;
use Notifications;

class BorderService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Border Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of border categories and borders.
    |
     */

    /**********************************************************************************************

    BORDER CATEGORIES

     **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Border\BorderCategory|bool
     */
    public function createBorderCategory($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateCategoryData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image             = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $category = BorderCategory::create($data);

            if ($image) {
                $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);
            }

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param  \App\Models\Border\BorderCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Border\BorderCategory|bool
     */
    public function updateBorderCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if (BorderCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) {
                throw new \Exception("The name has already been taken.");
            }

            $data = $this->populateCategoryData($data, $category);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image             = $data['image'];
                unset($data['image']);
            }

            $category->update($data);

            if ($image) {
                $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);
            }

            return $this->commitReturn($category);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param  array                               $data
     * @param  \App\Models\Border\BorderCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } else {
            $data['parsed_description'] = null;
        }

        if (isset($data['remove_image'])) {
            if ($category && $category->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Delete a category.
     *
     * @param  \App\Models\Border\BorderCategory  $category
     * @return bool
     */
    public function deleteBorderCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if (Border::where('border_category_id', $category->id)->exists()) {
                throw new \Exception("An border with this category exists. Please change its category first.");
            }

            if ($category->has_image) {
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName);
            }

            $category->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortBorderCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                BorderCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

    BORDERS

     **********************************************************************************************/

    /**
     * Creates a new border.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Border\Border
     */
    public function createBorder($data, $user)
    {
        DB::beginTransaction();

        try {
            if (isset($data['border_category_id']) && $data['border_category_id'] == 'none') {
                $data['border_category_id'] = null;
            }

            if ((isset($data['border_category_id']) && $data['border_category_id']) && ! BorderCategory::where('id', $data['border_category_id'])->exists()) {
                throw new \Exception("The selected border category is invalid.");
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $data = $this->populateData($data);

            $border = Border::create($data);

            if ($image) {
                $this->handleImage($image, $border->imagePath, $border->imageFileName);
            }

            return $this->commitReturn($border);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a border.
     *
     * @param  \App\Models\Border\Border  $border
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Border\Border
     */
    public function updateBorder($border, $data, $user)
    {
        DB::beginTransaction();

        try {
            if (isset($data['border_category_id']) && $data['border_category_id'] == 'none') {
                $data['border_category_id'] = null;
            }

            // More specific validation
            if (Border::where('name', $data['name'])->where('id', '!=', $border->id)->where('border_type', 'Default')->exists()) {
                throw new \Exception("The name has already been taken.");
            }

            if ((isset($data['border_category_id']) && $data['border_category_id']) && ! BorderCategory::where('id', $data['border_category_id'])->exists()) {
                throw new \Exception("The selected border category is invalid.");
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
            }

            $data = $this->populateData($data, $border);

            if (isset($data['image'])) {
                if ($image) {
                    $this->handleImage($image, $border->imagePath, $border->imageFileName);
                }

                unset($data['image']);
            }

            $border->update($data);

            return $this->commitReturn($border);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a border.
     *
     * @param  array                   $data
     * @param  \App\Models\Border\Border $border
     * @return array
     */
    private function populateData($data, $border = null)
    {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        } else {
            $data['parsed_description'] = null;
        }

        // Check toggle
        if (! isset($data['is_default'])) {
            $data['is_default'] = 0;
        }

        if (! isset($data['is_active'])) {
            $data['is_active'] = 0;
        }

        if (! isset($data['admin_only'])) {
            $data['admin_only'] = 0;
        }

        return $data;
    }

    /**
     * Deletes a border.
     *
     * @param  \App\Models\Border\Border  $border
     * @return bool
     */
    public function deleteBorder($border)
    {
        DB::beginTransaction();

        try {
            // Check first if the border is currently owned or if some other site feature uses it
            if (UserBorder::where('border_id', $border->id)->exists()) {
                throw new \Exception("At least one user currently owns this border. Please remove the border(s) before deleting it.");
            }

            DB::table('user_borders')->where('border_id', $border->id)->delete();

            $this->deleteImage($border->imagePath, $border->imageFileName);

            $border->variants()->delete();
            $border->topLayers()->delete();
            $border->bottomLayers()->delete();

            // Delete the border itself
            $border->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

    BORDER GRANTS

     **********************************************************************************************/

    /**
     * Admin function for granting borders to multiple users.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $staff
     * @return  bool
     */
    public function grantBorders($data, $staff)
    {
        DB::beginTransaction();

        try {
            // Process names
            $users = User::find($data['names']);
            if (count($users) != count($data['names'])) {
                throw new \Exception("An invalid user was selected.");
            }

            // Process borders
            $borders = Border::find($data['border_ids']);
            if (! $borders) {
                throw new \Exception("Invalid borders selected.");
            }

            foreach ($users as $user) {
                foreach ($borders as $border) {
                    if ($this->creditBorder($staff, $user, null, 'Staff Grant', array_only($data, ['data']), $border)) {
                        Notifications::create('BORDER_GRANT', $user, [
                            'border_name'    => $border->name,
                            'sender_url'     => $staff->url,
                            'sender_name'    => $staff->name,
                            'recipient_name' => $user->name,
                        ]);
                    } else {
                        throw new \Exception("Failed to credit borders to " . $user->name . ".");
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
     * Credits border to a user or character.
     *
     * @param  \App\Models\User\User                        $sender
     * @param  \App\Models\User\User                        $recipient
     * @param  \App\Models\Character\Character              $character
     * @param  string                                       $type
     * @param  string                                       $data
     * @param  \App\Models\Border\Border                    $border
     * @param  int                                          $quantity
     * @return  bool
     */
    public function creditBorder($sender, $recipient, $character, $type, $data, $border)
    {
        DB::beginTransaction();

        try {
            if (is_numeric($border)) {
                $border = Border::find($border);
            }

            // if($recipient->borders->contains($border)) throw new \Exception($recipient->name." already has the border ".$border->displayName);
            if ($recipient->borders->contains($border)) {
                flash($recipient->name . " already has the border " . $border->displayName, 'warning');
                return $this->commitReturn(false);
            }

            $record = UserBorder::where('user_id', $recipient->id)->where('border_id', $border->id)->first();
            if ($record) {
                // Laravel doesn't support composite primary keys, so directly updating the DB row here
                DB::table('user_borders')->where('user_id', $recipient->id)->where('border_id', $border->id);
            } else {
                $record = UserBorder::create(['user_id' => $recipient->id, 'border_id' => $border->id]);
            }

            if ($type && ! $this->createLog($sender ? $sender->id : null, $recipient ? $recipient->id : null,
                $character ? $character->id : null, $type, $data['data'], $border->id)) {
                throw new \Exception("Failed to create log.");
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Creates an border log.
     *
     * @param  int     $senderId
     * @param  string  $senderType
     * @param  int     $recipientId
     * @param  string  $recipientType
     * @param  int     $userBorderId
     * @param  string  $type
     * @param  string  $data
     * @param  int     $borderId
     * @return  int
     */
    public function createLog($senderId, $recipientId, $characterId, $type, $data, $borderId)
    {
        return DB::table('user_borders_log')->insert(
            [
                'sender_id'    => $senderId,
                'recipient_id' => $recipientId,
                'character_id' => $characterId,
                'border_id'    => $borderId,
                'log'          => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type'     => $type,
                'data'         => $data, // this should be just a string
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }

    /**********************************************************************************************

    VARIANTS

     **********************************************************************************************/

    /**
     * Creates a new variant for a border.
     *
     * @param mixed $border
     * @param mixed $data
     */
    public function createVariant($border, $data, $type)
    {
        DB::beginTransaction();

        try {
            // check name is unique
            if (Border::where('name', $data['name'])->where('parent_id', $border->id)->where('border_type', $type)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $data['parent_id']   = $border->id;
            $data['border_type'] = $type;

            $data = $this->populateData($data);

            $variant = Border::create($data);

            if ($image) {
                $this->handleImage($image, $variant->imagePath, $variant->imageFileName);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits the variants on a border.
     *
     * @param mixed $variant
     * @param mixed $data
     */
    public function editVariant($variant, $data, $type)
    {
        DB::beginTransaction();

        try {
            // check name is unique
            if (Border::where('name', $data['name'])->where('parent_id', $variant->parent->id)->where('id', '!=', $variant->id)->where('border_type', $variant->border_type)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
            }
            $data = $this->populateData($data, $variant);

            if (isset($data['image'])) {
                if ($image) {
                    $this->handleImage($image, $variant->imagePath, $variant->imageFileName);
                }

                unset($data['image']);
            }

            $variant->update($data);

            if ($type == 'top') {
                $check = 'top_border_id';
                $name  = 'Top Layer';
            } elseif ($type == 'bottom') {
                $check = 'bottom_border_id';
                $name  = 'Bottom Layer';
            } else {
                $check = 'border_variant_id';
                $name  = 'Variant';
            }

            if (isset($data['delete']) && $data['delete']) {

                // check that no user borders exist with this variant before deleting

                if (User::where($type, $variant->id)->exists()) {
                    throw new \Exception('At least one user has this variant as their border.');
                }

                $this->deleteImage($variant->imagePath, $variant->imageFileName);
                $variant->delete();
                flash($name . ' deleted successfully.')->success();
            } else {
                flash($name . ' updated successfully.')->success();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

    ETC

     **********************************************************************************************/

    /**
     * Create an associated item for a border
     *
     * @param mixed $border
     * @param mixed $user
     *
     * @return bool
     */
    public function createItem($border, $user)
    {
        DB::beginTransaction();

        try {
            //set the data to make the item....
            $data['name'] = $border->name . ' Border';
            $data['has_image'] = 1;

            // $data['hash'] = randomString(10);

            $image = Image::make($border->imagePath . '/' . $border->imageFileName);

            /* you can set some default data here, if desired
                $data['item_category_id'] = 1;
                $data['allow_transfer'] = 1;
                $data['is_released'] = 0;
            */

            //same with here, let's just nullify to prevent potential issues
            $data['data'] = json_encode([
                'rarity'  => null,
                'uses'    => null,
                'release' => null,
                'prompts' => null,
                'resell'  => null,
            ]);

            $item = Item::create($data);

            /*
                if (!$this->logAdminAction($user, 'Created Item', 'Created '.$item->displayName)) {
                    throw new \Exception('Failed to log admin action.');
                }
            */

            $image->save($item->imagePath . '/' . $item->imageFileName, 100, 'png');

            $tag = ItemTag::create([
                'item_id'   => $item->id,
                'tag'       => 'border',
                'is_active' => 1,
                'data'      => '{"borders":{"' . $border->id . '":1}}',
            ]);

            return $this->commitReturn($item);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

}
