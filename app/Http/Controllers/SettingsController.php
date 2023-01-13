<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function updateSetting (Request $request) {
        $request->validate([
            'user_id'=> 'required'
        ]);

        $setting = Settings::where("user_id", $request->user_id)->first();

        $setting->update($request->all());

        return response([
            'setting'=> $setting,
            'message' => 'Setting updated successfully',
            'status' => 'success'
        ], 201);
    }
}
