<?php

namespace App\Http\Controllers;

use App\Services\HolService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolController extends Controller {
    /**********************************************************************************************
    HIGHER OR LOWER
     **********************************************************************************************/

    /**
     * Shows the hol index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();

        if (($user->settings->hol_plays < config('lorekeeper.hol.hol_plays')) && ($user->holLastPlay() < Carbon::now()->startOfDay())) {
            $user->settings->hol_plays = config('lorekeeper.hol.hol_plays');
            $user->settings->save();
        }

        return view('hol.index', [
            'user' => $user,
        ]);
    }

    /**
     * play hol.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function playHol(HolService $service) {
        $user = Auth::user();

        if ($user->settings->hol_plays < 1) {
            flash('You can\'t play higher or lower more today.')->error();

            return redirect()->back();
        }

        $user->settings->hol_plays -= 1;
        $user->settings->hol_last_play = Carbon::now();
        $user->settings->save();

        // roll numba
        $number = mt_rand(2, 12);

        return view('hol.play', [
            'number' => $number,
        ]);
    }

    /**
     * make a guess.
     *
     * @param App\Services\HolService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postGuess(Request $request, HolService $service) {
        $data = $request->only(['guess', 'number']);
        if ($service->makeGuess($data, Auth::user())) {
            return redirect()->to('higher-or-lower');
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
