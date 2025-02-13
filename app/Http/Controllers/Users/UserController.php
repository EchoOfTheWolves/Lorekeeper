<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use Route;
use App\Models\User\User;

use App\Models\User\UserCurrency;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;
use App\Models\Gallery\Gallery;
use App\Models\Gallery\GallerySubmission;

use App\Models\User\UserItem;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Gallery\GalleryFavorite;
use App\Models\Gallery\GalleryCharacter;
use App\Models\Item\ItemLog;

use App\Models\Character\CharacterCategory;
use App\Models\Character\CharacterImage;
use App\Models\Character\Character;
use App\Models\Character\CharacterFolder;
use App\Models\Character\Sublist;

use App\Http\Controllers\Controller;
use App\Models\Shop\UserShop;


class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | Displays user profile pages.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $name = Route::current()->parameter('name');
        $this->user = User::where('name', $name)->first();
        if(!$this->user) abort(404);

        $this->user->updateCharacters();
        $this->user->updateCoCharacters();
        $this->user->updateArtDesignCredits();
    }

    /**
     * Shows a user's profile.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUser($name)
    {
        $characters = $this->user->characters();
        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) $characters->visible();

        $characters->orderByRaw('user_id = ? desc',[$this->user->id])->orderBy('sort', 'DESC')->get();
        
        return view('user.profile', [
            'user' => $this->user,
            'items' => $this->user->items()->where('count', '>', 0)->orderBy('user_items.updated_at', 'DESC')->take(4)->get(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get(),
            'characters' => $characters,
        ]);
    }

    /**
     * Shows a user's aliases.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserAliases($name)
    {
        $aliases = $this->user->aliases();
        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('edit_user_info'))) $aliases->visible();

        return view('user.aliases', [
            'user' => $this->user,
            'aliases' => $aliases->orderBy('is_primary_alias', 'DESC')->orderBy('site')->get(),
        ]);
    }

    /**
     * Shows a user's characters.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserCharacters($name)
    {
        $first = Character::myo(0)->where('coowner_id', $this->user->id);
        $query = Character::myo(0)->where('user_id', $this->user->id)->union($first);

        $imageQuery = CharacterImage::images(Auth::check() ? Auth::user() : null)->with('features')->with('rarity')->with('species')->with('features');

        if($sublists = Sublist::where('show_main', 0)->get())
        $subCategories = []; $subSpecies = [];
        {   foreach($sublists as $sublist)
            {
                $subCategories = array_merge($subCategories, $sublist->categories->pluck('id')->toArray());
                $subSpecies = array_merge($subSpecies, $sublist->species->pluck('id')->toArray());
            }
        }

        $query->whereNotIn('character_category_id', $subCategories);
        $imageQuery->whereNotIn('species_id', $subSpecies);

        $query->whereIn('id', $imageQuery->pluck('character_id'));

        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) $query->visible();
        $query = $query->orderBy('sort', 'DESC')->get()
        // group query folder, getting the name from the id
        ->groupBy(function($item) {
            return $item->folder ? $item->folder->name : 'Unsorted';
        });
        return view('user.characters', [
            'user' => $this->user,
            'characters' => $query,
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

        /**
     * Shows a user's characters.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserCharacterFolder($name, $folder)
    {
        $folder = CharacterFolder::where('name', $folder)->where('user_id', $this->user->id)->first();
        $query = Character::myo(0)->where('user_id', $this->user->id)->where('folder_id', $folder->id);
        $imageQuery = CharacterImage::images(Auth::check() ? Auth::user() : null)->with('features')->with('rarity')->with('species')->with('features');

        if($sublists = Sublist::where('show_main', 0)->get())
        $subCategories = []; $subSpecies = [];
        {   foreach($sublists as $sublist)
            {
                $subCategories = array_merge($subCategories, $sublist->categories->pluck('id')->toArray());
                $subSpecies = array_merge($subSpecies, $sublist->species->pluck('id')->toArray());
            }
        }

        $query->whereNotIn('character_category_id', $subCategories);
        $imageQuery->whereNotIn('species_id', $subSpecies);

        $query->whereIn('id', $imageQuery->pluck('character_id'));

        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) $query->visible();

        return view('user.character_folder', [
            'user' => $this->user,
            'folder' => $folder,
            'characters' => $query->orderByRaw('user_id = ? desc',[$this->user->id])->orderBy('sort', 'DESC')->get(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's sublist characters.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserSublist($name, $key)
    {
        $user = $this->user;
        $query = Character::myo(0)->where(function($query) use ($user) {
            $query->where('user_id', $user->id)
            ->orWhere('coowner_id', $user->id);
        });
        $imageQuery = CharacterImage::images(Auth::check() ? Auth::user() : null)->with('features')->with('rarity')->with('species')->with('features');

        $sublist = Sublist::where('key', $key)->first();
        if(!$sublist) abort(404);
        $subCategories = $sublist->categories->pluck('id')->toArray();
        $subSpecies = $sublist->species->pluck('id')->toArray();

        if($subCategories) $query->whereIn('character_category_id', $subCategories);
        if($subSpecies) $imageQuery->whereIn('species_id', $subSpecies);

        $query->whereIn('id', $imageQuery->pluck('character_id'));

        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) $query->visible();

        return view('user.sublist', [
            'user' => $user,
            'characters' => $query->orderByRaw('user_id = ? desc',[$user->id])->orderBy('sort', 'DESC')->get(),
            'sublist' => $sublist,
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's MYO slots.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserMyoSlots($name)
    {
        $myo = $this->user->myoSlots();
        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) $myo->visible();

        return view('user.myo_slots', [
            'user' => $this->user,
            'myos' => $myo->get(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's inventory.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserInventory($name)
    {
        $categories = ItemCategory::orderBy('sort', 'DESC')->get();
        $items = count($categories) ?
            $this->user->items()
                ->where('count', '>', 0)
                ->orderByRaw('FIELD(item_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')
                ->orderBy('name')
                ->orderBy('updated_at')
                ->get()
                ->groupBy(['item_category_id', 'id']) :
            $this->user->items()
                ->where('count', '>', 0)
                ->orderBy('name')
                ->orderBy('updated_at')
                ->get()
                ->groupBy(['item_category_id', 'id']);
        return view('user.inventory', [
            'user' => $this->user,
            'categories' => $categories->keyBy('id'),
            'items' => $items,
            'userOptions' => User::where('id', '!=', $this->user->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'user' => $this->user,
            'logs' => $this->user->getItemLogs(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's profile.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserBank($name)
    {
        $user = $this->user;
        return view('user.bank', [
            'user' => $this->user,
            'logs' => $this->user->getCurrencyLogs(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ] + (Auth::check() && Auth::user()->id == $this->user->id ? [
            'currencyOptions' => Currency::where('allow_user_to_user', 1)->where('is_user_owned', 1)->whereIn('id', UserCurrency::where('user_id', $this->user->id)->pluck('currency_id')->toArray())->orderBy('sort_user', 'DESC')->pluck('name', 'id')->toArray(),
            'userOptions' => User::where('id', '!=', Auth::user()->id)->orderBy('name')->pluck('name', 'id')->toArray()
        ] : []));
    }

    /**
     * Shows a user's currency logs.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserCurrencyLogs($name)
    {
        $user = $this->user;
        return view('user.currency_logs', [
            'user' => $this->user,
            'logs' => $this->user->getCurrencyLogs(0),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's item logs.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserItemLogs($name)
    {
        $user = $this->user;
        return view('user.item_logs', [
            'user' => $this->user,
            'logs' => $this->user->getItemLogs(0),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's character ownership logs.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserOwnershipLogs($name)
    {
        return view('user.ownership_logs', [
            'user' => $this->user,
            'logs' => $this->user->getOwnershipLogs(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's submissions.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserSubmissions($name)
    {
        return view('user.submission_logs', [
            'user' => $this->user,
            'logs' => $this->user->getSubmissions(Auth::check() ? Auth::user() : null),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's gallery submissions.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserGallery($name)
    {
        return view('user.gallery', [
            'user' => $this->user,
            'submissions' => $this->user->gallerySubmissions()->visible(Auth::user() ?? null)->paginate(20),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's gallery submission favorites.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserFavorites($name)
    {
        return view('user.favorites', [
            'user' => $this->user,
            'characters' => false,
            'favorites' => GallerySubmission::whereIn('id', $this->user->galleryFavorites()->pluck('gallery_submission_id')->toArray())->visible(Auth::user() ?? null)->orderBy('created_at', 'DESC')->paginate(20),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's gallery submission favorites that contain characters they own.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserOwnCharacterFavorites(Request $request, $name)
    {
        $user = $this->user;
        $userCharacters = Character::myo(0)->where(function($query) use ($user) {
            $query->where('user_id', $user->id)
            ->orWhere('coowner_id', $user->id);
        })->pluck('id')->toArray();
        $userFavorites = $user->galleryFavorites()->pluck('gallery_submission_id')->toArray();

        return view('user.favorites', [
            'user' => $this->user,
            'characters' => true,
            'favorites'  => $this->user->characters->count() ? GallerySubmission::whereIn('id', $userFavorites)->whereIn('id', GalleryCharacter::whereIn('character_id', $userCharacters)->pluck('gallery_submission_id')->toArray())->visible(Auth::check() ? Auth::user() : null)->accepted()->orderBy('created_at', 'DESC')->paginate(20)->appends($request->query()) : null,
            'sublists'   => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows a user's characters.
     *
     * @param  string  $name
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserShops($name)
    {
        $shops = UserShop::visible()->where('user_id', $this->user->id);

        return view('user.shops', [
            'user' => $this->user,
            'shops' => $shops->orderBy('sort', 'DESC')->get(),
            'sublists' => Sublist::orderBy('sort', 'DESC')->get()
        ]);
    }
}
