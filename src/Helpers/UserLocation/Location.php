<?php
namespace Kartikey\Core\Helpers\UserLocation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Stegback\Analytics\Repository\UserVisitRepository;

class Location
{
    public static function fetchCurrentLocation(UserVisitRepository $userVisitRepository)
    {
        if (Auth::check()) {
            // Fetch location by user ID
            $visit = $userVisitRepository->findWhere(['user_id' => Auth::user()->id])->first();
            // dd($visit);
            if ($visit) {
                return $visit->postal;  // Return the postal for the authenticated user
            }
        } else {
            $sessionId = session()->getId();
            // dd($sessionId);
            if ($sessionId) {
                // Try to find the location by session ID
                $visit = $userVisitRepository->findWhere(['session_id' => $sessionId])->first();
                if ($visit) {
                    return $visit->postal;  // Return the postal for the session
                }
            }
        }
        return null; // Return null if no location is found
    }
}
