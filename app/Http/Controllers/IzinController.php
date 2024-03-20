<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IzinController extends BaseController
{
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'isi' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['user_id'] = Auth::user()->id;
        $izin = Izin::create($input);

        return $this->sendResponse($izin, 'Izin created successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $req_id = $request->input('izin_id');
        $izin = Auth::user()->izin()->get();
        if ((Auth::user()->role->name == "admin") or (Auth::user()->role->name == "verifikator")) {
            $izin = Izin::all();
        }

        if ($req_id) {
            $res = $izin->where('id', $req_id);
            $res_st = "single izin";
        } else {
            $res = $izin->all();
            $res_st = "all izin";
        }

        if (count($res) > 0) {
            return $this->sendResponse($res, $res_st);
        } else {
            return $this->sendError('Izin not found.', []);
        }
    }

    public function update(Request $request): JsonResponse
    {
        if (Auth::user()->role->name != "user") {
            return $this->sendError('Endpoint Error, only for user.', []);
        }
        $validator = Validator::make($request->all(), [
            'izin_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $req_id = $request->input('izin_id');
        $izin = Auth::user()->izin()->where('id', $req_id)->get();

        if ($izin->count() <= 0) {
            return $this->sendError('Izin not found.', []);
        }

        $izin = $izin->first();

        if ($request->input('isi')) {
            $izin->isi = $request->input('isi');
        }
        if ($request->input('judul')) {
            $izin->judul = $request->input('judul');
        }
        $izin->save();

        return $this->sendResponse($izin, 'Izin updated successfully.');
    }

    public function batal(Request $request): JsonResponse
    {
        if (Auth::user()->role->name != "user") {
            return $this->sendError('Endpoint Error, only for user.', []);
        }
        $validator = Validator::make($request->all(), [
            'izin_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $req_id = $request->input('izin_id');
        $izin = Auth::user()->izin()->where('id', $req_id)->get();
        if ($izin->count() <= 0) {
            return $this->sendError('Izin not found.', []);
        }

        $izin = $izin->first();

        if ($izin->aktif) {
            $izin->aktif = false;
            $izin->save();
            return $this->sendResponse($izin, 'Izin dibatalkan.');
        } else {
            return $this->sendError('Izin sudah batal.', []);
        }
    }


    public function delete(Request $request): JsonResponse
    {
        if (Auth::user()->role->name != "user") {
            return $this->sendError('Endpoint Error, only for user.', []);
        }
        $validator = Validator::make($request->all(), [
            'izin_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $req_id = $request->input('izin_id');
        $izin = Auth::user()->izin()->where('id', $req_id)->get();
        if ($izin->count() <= 0) {
            return $this->sendError('Izin not found.', []);
        }

        $izin = $izin->first();

        $izin->delete();
        return $this->sendResponse($izin->id, 'Izin deleted successfully.');
    }

    public function verifyIzin(Request $request)
    {
        if (Auth::user()->role->name != 'verifikator') {
            return $this->sendError('Unauthorized Error.', []);
        }

        $validator = Validator::make($request->all(), [
            'izin_id' => 'required',
            'disetujui' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $req_id = $request->input('izin_id');

        $izin = Izin::where('id', $req_id)->first();

        if ($izin == null) {
            return $this->sendError('Izin not found.', []);
        }

        $izin->disetuji = $request->input('disetujui');
        if ($request->input('alasan')) {
            $izin->alasan = $request->input('alasan');
        }
        $izin->save();

        return $this->sendResponse($izin, 'Izin updated successfully.');
    }
}
