<?php

namespace Kartikey\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class WebServiceController extends Controller
{
    public function updateLocation(Request $request)
    {
        $validatedData = $request->validate([
            'pincode' => 'required|min:4|max:8',
        ], [
            'pincode.required' => 'Bitte geben Sie Ihren Postleitzahlenbereich ein.',
            'pincode.max' => 'Der Pincode darf nicht länger als 8 Ziffern sein.',
            'pincode.min' => 'Der Pincode muss mindestens 3-stellig sein.'

        ]);


        $pincode = $validatedData['pincode'];
        Event::dispatch('user.update.pincode', $pincode);

        return redirect()->back()->with('success', 'Standort erfolgreich aktualisiert.');
    }
}
